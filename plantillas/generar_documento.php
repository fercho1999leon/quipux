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

/*******************************************************************************
** Clase que genera los PDF de los documentos                                 **
** Retorna el path del archivo generado                                       **
*******************************************************************************/

class GenerarDocumento {
    var $db;
    var $ruta_raiz;

    var $registro; //Datos del documento
    var $registro_padre; //Datos del documento padre
    var $area;
    var $insitucion;
    var $remitente;
    var $destinatario;
    var $con_copia = "";

    var $opciones_impresion; //array
    var $lineas_especiales; //array que contiene temporalmente las líneas especiales (referencias, cca, responsables, anexos)
    var $formato_documento = 1; //Carga ciertas opciones especiales segín el tipo de documento (modos de escritura de destinatarios, remitentes, etc), por defecto tipo oficio
    var $numero_documento = ""; //radi_nume_text
    var $fecha_documento = "";
    var $plantilla_documento = "";

    var $path_pdf = ""; //Path donde se guarda el archivo en la bodega
    var $formato_pdf = ""; // Si imprime en italica o necesita otro tipo de configuracion de pagina en el html_a_pdf
    var $documento_html = ""; // Formato del documento según como está en la BDD
    var $datos_doc_destinatario = ""; //Datos de los destinatarios
    var $datos_doc_destinatario_lista = ""; //En caso que se envíe a una lista
    var $datos_doc_destinatario_al_pie = ""; // en caso que el destinatario deba ir al final del documento
    var $datos_doc_lugar_destinatario = "En su Despacho"; //En su despacho, Ciudad, Presente, etc.
    var $datos_doc_remitente = ""; //Datos del Remitente
    var $datos_doc_asunto = "";
    var $datos_doc_cuerpo = "";
    var $datos_doc_despedida = "Atentamente, ";
    var $datos_doc_dado_en = "";
    var $datos_doc_firma_digital = "&nbsp;<br>&nbsp;<br>&nbsp;<br>"; // Se carga el texto de Firma Digital, la imágen de la firma o espacios en blanco si es manual
    var $datos_doc_frase_remitente = ""; // Ejm: "Dios, Patria y Libertad"
    var $datos_doc_lineas_especiales = ""; // Texto definitivo con las líneas especiales (referencias, cca, responsables, anexos);


    function GenerarDocumento($db) {
	$this->db = $db;
        $this->ruta_raiz = $db->rutaRaiz;
    }

    /***************************************************************************
    ** Encera las variables para evitar que se mezclen los datos cuando se    **
    ** firman varios documentos a la vez                                      **
    ***************************************************************************/
    function limpiar_variables() {
        unset($this->registro); //Datos del documento
        unset($this->registro_padre); //Datos del documento padre
        unset($this->area);
        unset($this->insitucion);
        $this->remitente = "";
        $this->destinatario = "";
        $this->con_copia = "";

        unset($this->opciones_impresion); //array
        unset($this->lineas_especiales); //array que contiene temporalmente las líneas especiales (referencias, cca, responsables, anexos)
        $this->formato_documento = 1; //Carga ciertas opciones especiales segín el tipo de documento (modos de escritura de destinatarios, remitentes, etc), por defecto tipo oficio
        $this->numero_documento = ""; //radi_nume_text
        $this->fecha_documento = "";
        $this->plantilla_documento = "";

        $this->path_pdf = ""; //Path donde se guarda el archivo en la bodega
        $this->formato_pdf = ""; // Si imprime en italica o necesita otro tipo de configuracion de pagina en el html_a_pdf
        $this->documento_html = ""; // Formato del documento según como está en la BDD
        $this->datos_doc_destinatario = ""; //Datos de los destinatarios
        $this->datos_doc_destinatario_lista = ""; //En caso que se envíe a una lista
        $this->datos_doc_destinatario_al_pie = ""; // en caso que el destinatario deba ir al final del documento
        $this->datos_doc_lugar_destinatario = "En su Despacho"; //En su despacho, Ciudad, Presente, etc.
        $this->datos_doc_remitente = ""; //Datos del Remitente
        $this->datos_doc_asunto = "";
        $this->datos_doc_cuerpo = "";
        $this->datos_doc_despedida = "Atentamente, ";
        $this->datos_doc_dado_en = "";
        $this->datos_doc_firma_digital = "&nbsp;<br>&nbsp;<br>&nbsp;<br>"; // Se carga el texto de Firma Digital, la imágen de la firma o espacios en blanco si es manual
        $this->datos_doc_frase_remitente = ""; // Ejm: "Dios, Patria y Libertad"
        $this->datos_doc_lineas_especiales = ""; // Texto definitivo con las líneas especiales (referencias, cca, responsables, anexos);
        return;
    }

    /*******************************************************************************
    ** Llama a las funciones necesarias para la generación del PDF                **
    ** Retorna el path del archivo generado                                       **
    *******************************************************************************/
    function GenerarPDF($radi_nume, $firma_digital="no") {
        // Inicializo las variables y los datos necesarios para generar el pdf
       
        if (!$this->inicializar_variables($radi_nume, $firma_digital)) return $this->path_pdf;

        // Ejecuta algunas opciones de impresión para formatear ciertos datos del destinatario y del remitente
        $this->opciones_impresion_inicio();

        // Carga destinatario, remitente y las líneas especiales
        $this->cargar_datos_destinatario();
        $this->cargar_datos_remitente();
        $this->cargar_lineas_especiales_referencias();
        $this->cargar_lineas_especiales_anexos();
        $this->cargar_lineas_especiales_con_copia();
        $this->cargar_lineas_especiales_responsables();

        // Carga ciertos datos específicos de cada documento, por defecto: Oficios
        switch ($this->formato_documento) {
            case 2: //Memos
                break;
            case 3: //Nota Consular
                $this->generar_documento_tipo_nota_consular();
                break;
            case 4: //Resoluciones, Providencias
                $this->generar_documento_tipo_resolucion();
                break;
            case 5: // Acuerdos
                $this->generar_documento_tipo_acuerdo ();
                break;
            case 999: // Utilizada temporalmente en el desarrollo para cuadrar nuevos tipos de documentos
                $this->documento_html = '
                **QUIPUX_DATOS_DOC_ASUNTO**
                **QUIPUX_DATOS_DOC_DESTINATARIO**
                **QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**
                &nbsp;<br>&nbsp;<br>&nbsp;<br>
                **QUIPUX_DATOS_DOC_CUERPO**
                &nbsp;<br>
                **QUIPUX_DATOS_DOC_DESPEDIDA**
                &nbsp;<br>
                <b>**QUIPUX_DATOS_DOC_FRASE_REMITENTE**</b>
                &nbsp;<br>
                &nbsp;<br>
                **QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
                **QUIPUX_DATOS_DOC_REMITENTE**
                **QUIPUX_DATOS_DOC_DESTINATARIO_AL_PIE**
                &nbsp;<br>
                **QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**';
                break;
            default : //Oficios
                $this->generar_documento_tipo_oficio();
                break;
        }

        // Ejecuta las opciones de impresión antes de generar el HTML definitivo
        $this->opciones_impresion_fin();

        $this->cargar_lineas_especiales();
        
        // Reemplaza los datos generados y formateados según el formato establecido para cada tipo de documento
        // $this->documento_html se obtiene de la BDD
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_DESTINATARIO**", $this->datos_doc_destinatario, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_DESTINATARIO_AL_PIE**", $this->datos_doc_destinatario_al_pie, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**", $this->datos_doc_lugar_destinatario, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE**", $this->datos_doc_remitente, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_ASUNTO**", $this->datos_doc_asunto, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_CUERPO**", $this->datos_doc_cuerpo, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_DESPEDIDA**", $this->datos_doc_despedida, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_FRASE_REMITENTE**", $this->datos_doc_frase_remitente, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**", $this->datos_doc_firma_digital, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**", $this->datos_doc_lineas_especiales, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**", "&nbsp;<br>", $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**", "&nbsp;<br>", $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_DADO_EN**", $this->datos_doc_dado_en, $this->documento_html);
        
//echo str_replace("<", "&lt;", $this->documento_html);

        // Ponemos las cabeceras necesarias y generamos el PDF
        $this->documento_html = '<html>
                <head>
                    <title>'.$this->registro_padre['radi_nume_text'].'</title>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                </head>
                <body style="text-align: justify;">'.$this->documento_html."</body>
            </html>";
        
        $this->generar_archivo_pdf();
        // Retorna el path del archivo generado
        return $this->path_pdf;

    }

    /*******************************************************************************
    ** Inicializa las variables necesarias para la generación del PDF según los   **
    ** datos de la tabla radicado como usuarios remitente, destinatario, fechas   **
    ** números de documentos, textos, etc.                                        **
    *******************************************************************************/
    function inicializar_variables($radi_nume, $firma_digital) {
        include_once $this->ruta_raiz."/obtenerdatos.php";
        include_once $this->ruta_raiz."/class_control/class_gen.php";
        $this->limpiar_variables();

        $this->registro = ObtenerDatosRadicado($radi_nume,$this->db);
        // Validamos si es un documento de entrada
        if (substr($this->registro["radi_nume_temp"], -1) == "2") return false;
        //Validamos si ya tiene un documento registrado
        if ($this->registro["arch_codi"]>0) {
            $this->path_pdf = $this->registro["arch_codi"];
            return false;
        } elseif (trim($this->registro["radi_path"])!="") {
            $this->path_pdf = $this->registro["radi_path"];
            return false;
        }

        // Inicializamos el registro del radicado padre
        if (substr(trim($this->registro["radi_nume_radi"]),-1) == "1")
            $this->registro_padre = ObtenerDatosRadicado($this->registro["radi_nume_temp"],$this->db);
        else 
            $this->registro_padre = $this->registro;

        // Buscamos el texto del documento
        $rs = $this->db->query("select text_texto from radi_texto where text_codi=".$this->registro_padre["radi_codi_texto"]);
        $this->datos_doc_cuerpo = $this->limpiar_cuerpo_documento($rs->fields["TEXT_TEXTO"]);
        $this->datos_doc_asunto = $this->formatear_datos_documento($this->registro_padre["radi_asunto"]);

        // Inicializamos otros datos necesarios
        $this->remitente = ObtenerListaUsuariosDocumento ($this->db, $radi_nume, "R");
        $this->destinatario = ObtenerListaUsuariosDocumento ($this->db, $radi_nume, "D");
        $this->con_copia = ObtenerListaUsuariosDocumento ($this->db, $radi_nume, "C");
        $this->insitucion = ObtenerDatosInstitucion($this->registro_padre["inst_actu"],$this->db);
        $this->area = ObtenerDatosDependencia($this->remitente[0]["depe_codi"],$this->db);
        $this->plantilla_documento = "";
        if ($this->registro_padre["usar_plantilla"] == 1) {
            $this->plantilla_documento = $this->ruta_raiz."/bodega/plantillas/".$this->area["plantilla"].".pdf";
            if (!is_file($this->plantilla_documento)) $this->plantilla_documento = "";
        }



        // Inicializamos el formato del documento y las opciones de impresión
        $rs_tiporad = $this->db->conn->query("select * from tiporad where trad_codigo=".$this->registro_padre["radi_tipo"]);
        $this->documento_html = $rs_tiporad->fields["TRAD_FORMATO"];
        $this->formato_documento = 0 + $rs_tiporad->fields["TRAD_FORMATO_TIPO"];
        $this->cargar_opciones_impresion($rs_tiporad->fields["TRAD_OPC_IMPRESION"]);

        // Inicializamos el No. de documento
        $this->numero_documento = $rs_tiporad->fields["TRAD_DESCR"]." Nro. ".$this->registro_padre["radi_nume_text"];

        // Seteamos la fecha
        $gen_fecha = new CLASS_GEN();
        if($this->registro_padre["estado"]=="1" or $this->registro_padre["estado"]=="7" )
            $this->fecha_documento = $gen_fecha->traducefecha(date("Y-m-d"));
        else
            $this->fecha_documento = $gen_fecha->traducefecha($this->registro_padre["radi_fecha"]);
        $this->fecha_documento = trim($this->remitente[0]["usua_ciudad"]).", ".$this->fecha_documento;

        // Verificamos si firma digitalmente el archivo
        if ($firma_digital=="si") {
            if ($this->registro["estado"]==5) { //Si es para un ciudadano y el remitente tiene escaneada su firma
                if (trim($this->remitente[0]["usua_firma_path"]) != "")
                    $this->datos_doc_firma_digital = "<img src='".trim($this->remitente[0]["usua_firma_path"])."'><br>";
            } elseif ($this->registro_padre["estado"]==3)
                $this->datos_doc_firma_digital = "&nbsp;<br><i><b><h6><font color='blue'>Documento firmado electr&oacute;nicamente</font></h6></b></i>";
        }
        //$this->datos_doc_firma_digital = "&nbsp;<br><i><b><h6><font color='blue'>Documento firmado electr&oacute;nicamente</font></h6></b></i>";
        //$this->datos_doc_firma_digital = "<img src='$nombre_servidor/bodega/firmas/3.jpeg'>";

        $this->datos_doc_frase_remitente = "<br><b>".$this->formatear_datos_documento(trim($this->insitucion["fraseDespedida"]),"U")."</b>";

        return true;
    }

    /***********************************************************************************
    ** Carga la lista de opciones de impresión para cada tipo de documento            **
    ** dependiendo de lo ingresado en el campo trad_opc_impresion de la tabla tiporad **
    ***********************************************************************************/
    function cargar_opciones_impresion ($opc_impresion) {
        if (trim($opc_impresion) == "") return;

        $sql="select * from opciones_impresion where radi_nume_radi=".$this->registro_padre["radi_nume_radi"]." order by opc_imp_codi desc" ;
        $rs = $this->db->query($sql);
        if (!$rs or $rs->EOF) return;
        
        $opcion = explode(",", trim($opc_impresion));
        foreach ($rs->fields as $campo => $dato) {
            $campo = strtolower($campo);
            $this->opciones_impresion[$campo] = "";
            // Si está en la lista carga el dato ingresado en opciones de impresion caso contrario pone vacío
            for ($i=0 ; $i<count($opcion); ++$i) {
                if ($campo==trim($opcion[$i])) {
                    $this->opciones_impresion[$campo] = $dato;
                    $i = count($opcion);
                }
            }
        } //Fin foreach
    }

    /*******************************************************************************
    ** Modifica la información del destinatario según los datos ingresados en las **
    ** opciones de impresión y según la opción seleccionada en el combo de tipos  **
    ** de impresión de los destinatarios                                          **
    *******************************************************************************/
    function opciones_impresion_inicio () {

        if (count($this->destinatario) == 1) { //Si hay un solo destinatario aplican las siguientes opciones de impresion
            // Añade al final del nombre del destinatario un texto
            if (isset($this->opciones_impresion["opc_imp_firmantes"]) and trim($this->opciones_impresion["opc_imp_firmantes"])!="")
                $this->destinatario[0]["usua_apellido"] .= " ".trim($this->opciones_impresion["opc_imp_firmantes"]);

            // Añade al final del nombre de la institucion un texto
            if (isset($this->opciones_impresion["opc_imp_ext_institucion"]) and trim($this->opciones_impresion["opc_imp_ext_institucion"])!="")
                $this->destinatario[0]["usua_institucion"] .= " ".trim($this->opciones_impresion["opc_imp_ext_institucion"]);

            // Cambia el titulo del usuario
            if (isset($this->opciones_impresion["opc_imp_titulo_natural"]) and trim($this->opciones_impresion["opc_imp_titulo_natural"])!="")
                $this->destinatario[0]["usua_titulo"] = $this->opciones_impresion["opc_imp_titulo_natural"];

            // Cambia el cargo del usuario
            if (isset($this->opciones_impresion["opc_imp_cargo_cabecera"]) and trim($this->opciones_impresion["opc_imp_cargo_cabecera"])!="")
                $this->destinatario[0]["usua_cargo_cabecera"] = $this->opciones_impresion["opc_imp_cargo_cabecera"];
        }

        if (isset($this->opciones_impresion["opc_imp_destino_destinatario"]) and trim($this->opciones_impresion["opc_imp_destino_destinatario"])!="")
            $this->datos_doc_lugar_destinatario = $this->formatear_datos_documento($this->opciones_impresion["opc_imp_destino_destinatario"], "I");

        // Cargamos según el combo de tipos de impresión
        foreach ($this->destinatario as $i => $usr) {
            switch ($this->registro_padre["radi_tipo_impresion"]) {
                case 1: //(título, nombre, cargo, institución)
                    break;
                case 2: //(cargo, institución)
                    $this->destinatario[$i]["usua_abr_titulo"] = "";
                    $this->destinatario[$i]["usua_titulo"] = "";
                    $this->destinatario[$i]["usua_nomb"] = "";
                    $this->destinatario[$i]["usua_apellido"] = "";
                    break;
                case 4: //(título, nombre, cargo)
                    $this->destinatario[$i]["usua_institucion"] = "";
                    break;
                case 5: //(título, nombre, institución)
                    $this->destinatario[$i]["usua_cargo_cabecera"] = "";
                    $this->destinatario[$i]["usua_cargo"] = "";
                    break;
                case 6: //(título, cargo, institución)
                    $this->destinatario[$i]["usua_abr_titulo"] = $this->destinatario[$i]["usua_titulo"]; //Para memos
                    $this->destinatario[$i]["usua_nomb"] = "";
                    $this->destinatario[$i]["usua_apellido"] = "";
                    break;
            }
        } // fin foreach

    }

    /*************************************************************************************
    ** Modifica los datos precargados en base a las opciones de impresión seleccionadas **
    *************************************************************************************/
    function opciones_impresion_fin () {
        // En el caso que se imprima el nombre de la lista
        if ($this->registro_padre["radi_tipo_impresion"] == 3)
            $this->datos_doc_destinatario = $this->datos_doc_destinatario_lista;

        // Ocultar numero de documento
        if (isset($this->opciones_impresion["opc_imp_ocultar_nume_radi"]) and ($this->opciones_impresion["opc_imp_ocultar_nume_radi"]==1 or $this->registro_padre["radi_tipo"]==7))
            $this->numero_documento = "";

        // Justificar datos firmante
        if (isset($this->opciones_impresion["opc_imp_justificar_firma"]) and $this->opciones_impresion["opc_imp_justificar_firma"]==1) {
            $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**", "<center>", $this->documento_html);
            $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**", "</center>", $this->documento_html);
        } 

        // frase del remitente, ejm: DIOS, PATRIA Y LIBERTAD
        if (isset($this->opciones_impresion["opc_imp_frase_remitente"]) and trim($this->opciones_impresion["opc_imp_frase_remitente"])!="")
            $this->datos_doc_frase_remitente = "&nbsp;<br><b>".$this->formatear_datos_documento($this->opciones_impresion["opc_imp_frase_remitente"],"U")."</b>";
        if (isset($this->opciones_impresion["opc_imp_ocultar_frase_rem"]) and $this->opciones_impresion["opc_imp_ocultar_frase_rem"]==1)
            $this->datos_doc_frase_remitente = "";

        // Despedida, Ejm: "Atentamente,"
        if (isset($this->opciones_impresion["opc_imp_despedida"]) and trim($this->opciones_impresion["opc_imp_despedida"])!="")
            $this->datos_doc_despedida = $this->formatear_datos_documento($this->opciones_impresion["opc_imp_despedida"]);
        if (isset($this->opciones_impresion["opc_imp_ocultar_atentamente"]) and $this->opciones_impresion["opc_imp_ocultar_atentamente"]==1)
            $this->datos_doc_despedida = "";

        // Dado en ...
        $this->datos_doc_dado_en = "Dado en " . $this->remitente[0]["usua_ciudad"] . ", ";
        if (isset($this->opciones_impresion["opc_imp_ciudad_dado_en"]) and trim($this->opciones_impresion["opc_imp_ciudad_dado_en"])!="")
            $this->datos_doc_dado_en = "Dado en " . $this->opciones_impresion["opc_imp_ciudad_dado_en"] . ", ";
        if($this->registro_padre["estado"]=="1" or $this->registro_padre["estado"]=="7" )
            $this->datos_doc_dado_en .= fechaAtexto(date('Y-m-d'));
        else
            $this->datos_doc_dado_en .= fechaAtexto($this->registro_padre["radi_fecha"]);

        // Ocultar Lineas especiales
        if (isset($this->opciones_impresion["opc_imp_ocultar_anexo"]) and $this->opciones_impresion["opc_imp_ocultar_anexo"]==1)
            $this->lineas_especiales["anexos"] = "";
        if (isset($this->opciones_impresion["opc_imp_ocultar_referencia"]) and $this->opciones_impresion["opc_imp_ocultar_referencia"]==1)
            $this->lineas_especiales["referencias"] = "";
        if (isset($this->opciones_impresion["opc_imp_ocultar_sumillas"]) and $this->opciones_impresion["opc_imp_ocultar_sumillas"]==1)
            $this->lineas_especiales["responsables"] = "";

        if (isset($this->opciones_impresion["opc_imp_letra_italica"]) and $this->opciones_impresion["opc_imp_letra_italica"]==1)
            $this->formato_pdf = "I";
        if (isset($this->opciones_impresion["opc_imp_ocultar_asunto"]) and $this->opciones_impresion["opc_imp_ocultar_asunto"]==1)
            $this->datos_doc_asunto = "";

        //Muestra el destinatario al final del documento luego del firmante
        if (isset($this->opciones_impresion["opc_imp_mostrar_para"]) and $this->opciones_impresion["opc_imp_mostrar_para"]==1) {
            $this->datos_doc_destinatario_al_pie = "&nbsp;<br>".$this->datos_doc_destinatario;
            $this->datos_doc_destinatario = "";
            if (strpos($this->documento_html, "**QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**") !== false) {
                $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**", "", $this->documento_html);
                $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_DESTINATARIO_AL_PIE**", "**QUIPUX_DATOS_DOC_DESTINATARIO_AL_PIE** **QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**", $this->documento_html);
            }
        }

        return;
    }


    /***************************************************************************
    ** Se modifican ciertos parámetros dependiendo del tipo de documento      **
    ***************************************************************************/
    function generar_documento_tipo_oficio () {
        $this->datos_doc_asunto = "<b>Asunto: </b>" . $this->datos_doc_asunto . "<br>&nbsp;<br>&nbsp;<br>";
        if (!isset($this->opciones_impresion["opc_imp_mostrar_para"]) or $this->opciones_impresion["opc_imp_mostrar_para"]!=1)
            $this->datos_doc_lugar_destinatario .= "&nbsp;<br>&nbsp;<br>&nbsp;<br>";
        return;
    }

    function generar_documento_tipo_nota_consular () {

        // Borramos líneas especiales, solo queda CCA
        $this->opciones_impresion["opc_imp_ocultar_anexo"] = 1;
        $this->opciones_impresion["opc_imp_ocultar_referencia"] = 1;
        $this->opciones_impresion["opc_imp_ocultar_sumillas"] = 1;
        $this->opciones_impresion["opc_imp_justificar_firma"] = 1;
        if (!isset($this->opciones_impresion["opc_imp_destino_destinatario"]) or trim($this->opciones_impresion["opc_imp_destino_destinatario"])=="")
            $this->datos_doc_lugar_destinatario = "Ciudad.-";
        
        if (isset($this->opciones_impresion["opc_imp_tipo_nota"])) {
            switch ($this->opciones_impresion["opc_imp_tipo_nota"]) {
                case 1: //Nota Diplomática
                    $this->datos_doc_remitente .= "&nbsp;<br>";
                    break;
                case 2: //Nota Reversal
                    // Muestra el destinatario al pie
                    $this->opciones_impresion["opc_imp_mostrar_para"] = 1;
                    break;
                default: // Nota Verbal
                    //Sin firmante; fecha y destinatario al pie; solo se imprime "A la honorable" + la institución del destinatario
                    $this->documento_html = '
                        **QUIPUX_DATOS_DOC_CUERPO**
                        <table width="100%"><tr><td align="right"><font size=3><b>'.$this->fecha_documento.'</b></font></td></tr></table>
                        **QUIPUX_DATOS_DOC_DESTINATARIO**
                        **QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**';

                    $this->fecha_documento = "";
                    $this->datos_doc_destinatario = "&nbsp;<br>";
                    if (trim($this->destinatario[0]["usua_titulo"])!="") $this->datos_doc_destinatario .= "<b>".$this->formatear_datos_documento($this->destinatario[0]["usua_titulo"], "I")."</b><br>";
                    if (trim($this->destinatario[0]["usua_institucion"])!="") $this->datos_doc_destinatario .= "<b>".$this->formatear_datos_documento($this->destinatario[0]["usua_institucion"],"U")."</b><br>";
                    break;
            }
        }
        $this->datos_doc_destinatario .= $this->datos_doc_lugar_destinatario. "&nbsp;<br>&nbsp;<br>";

        return;
    }

    function generar_documento_tipo_resolucion () {
        $this->opciones_impresion["opc_imp_justificar_firma"] = 1;
        
        $remitente_nombre = $this->formatear_datos_documento($this->remitente[0]["usua_nomb"]." ".$this->remitente[0]["usua_apellido"], "I");
        $remitente_cargo = $this->formatear_datos_documento($this->remitente[0]["usua_cargo"],"I");
        $remitente_institucion = $this->formatear_datos_documento($this->remitente[0]["usua_institucion"],"U");
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_NOMBRE**", $remitente_nombre, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_CARGO**", $remitente_cargo, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_INSTITUCION**", $remitente_institucion, $this->documento_html);
        return;
    }

    function generar_documento_tipo_acuerdo () {
        $this->opciones_impresion["opc_imp_ocultar_anexo"] = 1;
        $this->opciones_impresion["opc_imp_ocultar_referencia"] = 1;
        $this->opciones_impresion["opc_imp_ocultar_sumillas"] = 1;
        $this->opciones_impresion["opc_imp_justificar_firma"] = 1;
        $this->opciones_impresion["opc_imp_ocultar_nume_radi"] = 1;
        $this->fecha_documento = "";
        $this->formato_pdf = "A";

        $remitente_nombre = $this->formatear_datos_documento($this->remitente[0]["usua_abr_titulo"]." ".$this->remitente[0]["usua_nomb"]." ".$this->remitente[0]["usua_apellido"], "U");
        $remitente_cargo = $this->formatear_datos_documento($this->remitente[0]["usua_cargo"],"U");
        $remitente_institucion = $this->formatear_datos_documento($this->remitente[0]["usua_institucion"],"U");
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_NOMBRE**", $remitente_nombre, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_CARGO**", $remitente_cargo, $this->documento_html);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_REMITENTE_INSTITUCION**", $remitente_institucion, $this->documento_html);

        $this->numero_documento = str_replace("Acuerdo", "ACUERDO", $this->numero_documento);
        $this->documento_html = str_replace("**QUIPUX_DATOS_DOC_NUMERO_DOCUMENTO**", $this->numero_documento, $this->documento_html);
        return;
    }

    /***************************************************************************
    ** Se cargan los datos del destinatario según el formato establecido      **
    ** para cada tipo de documento, por defecto oficios                       **
    ***************************************************************************/
    function cargar_datos_destinatario () {
        // Datos destinatarios
        $this->datos_doc_destinatario = "";
        $label_memo = "<b>PARA:</b>";
        for ($i=0; $i<count($this->destinatario); ++$i) {
            $usr = $this->destinatario[$i];
            switch ($this->formato_documento) {
                case 2: //Memos
                    $this->datos_doc_destinatario .= '
                    <table border="0" cellpadding="0" cellspacing="0" width="800px">
                        <tr>
                            <td width="120px" valign="top">'.$label_memo.'</b></td>
                            <td width="680px" valign="top">'.
                                trim($this->formatear_datos_documento($usr["usua_abr_titulo"], "I")." ".$this->formatear_datos_documento($usr["usua_nomb"]." ".$usr["usua_apellido"], "I")).
                                "<br><b>".$this->formatear_datos_documento($usr["usua_cargo"],"I").'</b>
                            </td>
                        </tr>
                    </table>';
                    $label_memo = "&nbsp;";
//                    $this->datos_doc_destinatario .= trim($this->formatear_datos_documento($usr["usua_abr_titulo"], "I")." ".$this->formatear_datos_documento($usr["usua_nomb"]." ".$usr["usua_apellido"], "I"));
//                    $this->datos_doc_destinatario .= "<br><b>".$this->formatear_datos_documento($usr["usua_cargo"],"I")."</b><br>&nbsp;<br>";
                    break;
                default : //Oficios
                    if ($this->datos_doc_destinatario != "") $this->datos_doc_destinatario .= "&nbsp;<br>";
                    if (trim($usr["usua_titulo"])!="") $this->datos_doc_destinatario .= $this->formatear_datos_documento($usr["usua_titulo"], "I")."<br>";
                    if (trim($usr["usua_nomb"].$usr["usua_apellido"])!="") $this->datos_doc_destinatario .= $this->formatear_datos_documento($usr["usua_nomb"]." ".$usr["usua_apellido"], "I")."<br>";
                    if (trim($usr["usua_cargo_cabecera"])!="") $this->datos_doc_destinatario .= "<b>".$this->formatear_datos_documento($usr["usua_cargo_cabecera"],"I")."</b><br>";
                    if (trim($usr["usua_institucion"])!="") $this->datos_doc_destinatario .= "<b>".$this->formatear_datos_documento($usr["usua_institucion"],"U")."</b><br>";
                    break;
            }
        }
        // En caso que se envie a una lista
        if (substr(trim($this->destinatario[0]["lista_nombre"]),-4)=="<br>")
            $this->destinatario[0]["lista_nombre"] = substr(trim($this->destinatario[0]["lista_nombre"]),0,-4);
        $this->datos_doc_destinatario_lista = "<b>".$this->formatear_datos_documento($this->destinatario[0]["lista_nombre"],"I")."<br></b>";
        return;
    }

    /***************************************************************************
    ** Se cargan los datos del firmante según el formato establecido          **
    ** para cada tipo de documento, por defecto oficios                       **
    ***************************************************************************/
    function cargar_datos_remitente () {
        // Datos firmante
        $this->datos_doc_remitente = "";
        for ($i=0; $i<count($this->remitente); ++$i) {
            $usr = $this->remitente[$i];
            //Quitamos las abreviaturas de Sr., Sra, etc. del titulo del remitente
            $abr_titulo = str_ireplace(array("sr.", "sra.", "srita.", "sres."), "", $usr["usua_abr_titulo"]);
            if (trim($abr_titulo) != "") $usr["usua_abr_titulo"] = $abr_titulo;

            if ($this->datos_doc_remitente != "") $this->datos_doc_remitente .= "&nbsp;<br>";
            $this->datos_doc_remitente .= $this->formatear_datos_documento($usr["usua_abr_titulo"], "I");
            $this->datos_doc_remitente .= " ".$this->formatear_datos_documento($usr["usua_nomb"]." ".$usr["usua_apellido"], "I");
            switch ($this->formato_documento) {
                case 3: //Notas Consulares
                    $this->datos_doc_remitente .= "<br><b>".$this->formatear_datos_documento($usr["usua_cargo"],"I")."</b>";
                    break;
                default : // todos los demás
                    $this->datos_doc_remitente .= "<br><b>".$this->formatear_datos_documento($usr["usua_cargo"],"U")."</b>";
                    break;
            }
        }
        return;
    }

    /*****************************************************************************
    ** Se cargan las líneas especiales; cada una de las variables fue cargada   **
    ** con anterioreidad ya que se las modifica con las opciones de impresión   **
    *****************************************************************************/
    function cargar_lineas_especiales() {
        $this->datos_doc_lineas_especiales = '' .
                    $this->lineas_especiales["referencias"] .
                    $this->lineas_especiales["anexos"] .
                    $this->lineas_especiales["cca"].
                    $this->lineas_especiales["responsables"];
        return;
    }

    function cargar_lineas_especiales_responsables() {
        $this->lineas_especiales["responsables"] = "";
        $responsables = "";
        // Se excluyen las sumillas del firmante
        $usuarios = "0,".(0+$this->remitente[0]["usua_codi"]);
        
        // Cargar responsable de area
        $sql = "select usua_codi, usua_sumilla, usua_nomb, usua_apellido from usuarios 
                where depe_codi=".(0+$this->remitente[0]["depe_codi"])." and usua_codi not in ($usuarios) and usua_responsable_area=1";
        $rs = $this->db->conn->query($sql);
        if ($rs && !$rs->EOF) {
            if (trim($rs->fields["USUA_SUMILLA"])!= "") // Si no tiene sumilla se ponen las iniciales
                $responsables = trim($rs->fields["USUA_SUMILLA"]);
            else
                $responsables = substr(trim($rs->fields["USUA_NOMB"]),0,1).substr(trim($rs->fields["USUA_APELLIDO"]),0,1);
            $responsables = strtoupper($responsables);
            // Se excluye al responsable de área para que no se vuelva a imprimir si modificó el documento
            $usuarios .= ",".$rs->fields["USUA_CODI"];
        }

        // Se cargan todas las personas que participaron en la elaboración del documento excepto el firmante y el responsable del área
        $sql = "select u.usua_codi, u.usua_sumilla, u.usua_nomb, u.usua_apellido
                from (select usua_codi_ori, max(hist_codi) as hist_codi from hist_eventos where sgd_ttr_codigo in (66,2,11,65,9)
                        and radi_nume_radi=".$this->registro_padre["radi_nume_radi"]." and usua_codi_ori not in ($usuarios) group by usua_codi_ori) as h
                    left outer join usuarios u on h.usua_codi_ori=u.usua_codi
                order by h.hist_codi";
//        echo $sql;
        $rs = $this->db->conn->query($sql);
        while ($rs && !$rs->EOF) {
            if ($responsables != "") $responsables .= "/";
            if (trim($rs->fields["USUA_SUMILLA"])!= "") // Si no tiene sumilla se ponen las iniciales
                $responsables .= trim($rs->fields["USUA_SUMILLA"]);
            else
                $responsables .= strtolower(substr(trim($rs->fields["USUA_NOMB"]),0,1).substr(trim($rs->fields["USUA_APELLIDO"]),0,1));
            $rs->MoveNext();
        }

        $this->lineas_especiales["responsables"] = "<dl><dt><font size=2>".$this->formatear_datos_documento($responsables)."</font></dt></dl>";
        return;
    }

    function cargar_lineas_especiales_referencias() {
        $this->lineas_especiales["referencias"] = "";
        if (trim($this->registro_padre["radi_referencia"]) == "") return;
        $this->lineas_especiales["referencias"] = '
                    <dl>
                        <dt><font size=2>Referencias:</font></dt>
                        <dd><font size=2> - '.$this->formatear_datos_documento($this->registro_padre["radi_referencia"]).'</font></dd>
                    </dl>';
        return;
    }

    function cargar_lineas_especiales_con_copia() {
        $this->lineas_especiales["cca"] = "";
        if (count($this->con_copia) == 0) return;

        $cca = "";
        for ($i=0; $i<count($this->con_copia); ++$i) {
            $usr = $this->con_copia[$i];
            if ($cca != "") $cca .= "<font size=1>&nbsp;<br></font>";
            switch ($this->formato_documento) {
                case 2: //Memos
                    $cca .= trim($this->formatear_datos_documento($usr["usua_abr_titulo"], "I")." ".$this->formatear_datos_documento($usr["usua_nomb"]." ".$usr["usua_apellido"], "I"));
                    $cca .= "<br><b>".$this->formatear_datos_documento($usr["usua_cargo"],"I")."</b><br>";
                    break;
                default : //Oficios
                    if (trim($usr["usua_titulo"])!="") $cca .= $this->formatear_datos_documento($usr["usua_titulo"], "I")."<br>";
                    if (trim($usr["usua_nomb"].$usr["usua_apellido"])!="") $cca .= $this->formatear_datos_documento($usr["usua_nomb"]." ".$usr["usua_apellido"], "I")."<br>";
                    if (trim($usr["usua_cargo_cabecera"])!="") $cca .= "<b>".$this->formatear_datos_documento($usr["usua_cargo_cabecera"],"I")."</b><br>";
                    if (trim($usr["usua_institucion"])!="" and trim($usr["usua_institucion"])!=trim($this->remitente[0]["usua_institucion"]))
                        $cca .= "<b>".$this->formatear_datos_documento($usr["usua_institucion"],"U")."</b><br>";
                    break;
            }
        }
        $this->lineas_especiales["cca"] = '
                    <dl>
                        <dt><font size=2>Copia:</font></dt>
                        <dd><font size=2>'.$cca.'</font></dd>
                    </dl>';

        return;
    }

    function cargar_lineas_especiales_anexos() {
        $this->lineas_especiales["anexos"] = "";

        $descripcion_anexos = $this->formatear_datos_documento($this->registro_padre["radi_desc_anexos"]);
        if ($descripcion_anexos != "") $descripcion_anexos .= "<br>";
        $lista_anexos = "";
        
        // Cargo la lista de archivos anexos
        $sql = "select anex_nombre, anex_desc from anexos
		where anex_radi_nume=".$this->registro_padre["radi_nume_radi"]." and anex_borrado='N'
		order by anex_codigo";
        $rs = $this->db->conn->query($sql);
        while(!$rs->EOF) {
            if ($lista_anexos != "") $lista_anexos .= "<br>";
            if(trim($rs->fields["ANEX_DESC"])!='')
                $lista_anexos .= " - " . $this->formatear_datos_documento($rs->fields["ANEX_DESC"]);
            else
                $lista_anexos .= " - " . $this->formatear_datos_documento($rs->fields["ANEX_NOMBRE"]);
            $rs->MoveNext();
        }

        // Si no hay anexos
        if ($descripcion_anexos.$lista_anexos != "") {
            $this->lineas_especiales["anexos"] = '
                        <dl>
                            <dt><font size=2>Anexos: '.$descripcion_anexos.'</font></dt>';
            if ($lista_anexos != "")
                $this->lineas_especiales["anexos"] .= '<dd><font size=2>'.$lista_anexos.'</font></dd>';
            $this->lineas_especiales["anexos"] .= '</dl>';
        }
        return;
    }


    /***************************************************************************
    ** Llama al módulo HTML_A_PDF para que se genere el archivo               **
    ** Si es un documento ya enviado graba el archivo en la bodega y          **
    ** actualiza el path en la tabla radicado                                 **
    ***************************************************************************/
    function generar_archivo_pdf() {
        $ruta_raiz = $this->ruta_raiz;
        include $this->ruta_raiz."/config.php";
        require_once $this->ruta_raiz."/interconexion/generar_pdf.php";
        $pdf = ws_generar_pdf_base64($this->documento_html, $this->plantilla_documento, $servidor_pdf, $this->registro_padre["estado"], $this->numero_documento, $this->fecha_documento, $this->registro_padre["ajust_texto"], $this->formato_pdf);
        if ($pdf == "0") return;

        if (in_array($this->registro["estado"],array(1,3,4,7))) { // si es temporal
            $this->path_pdf = "/tmp/".$this->registro["radi_nume_radi"].".pdf";
            file_put_contents($this->ruta_raiz."/bodega".$this->path_pdf, base64_decode($pdf));
            return;
        } else { // Guardo el PDF en la bodega y actualizo el path del documento si no es temporal
            require_once $this->ruta_raiz."/include/db/ConnectionHandler.php";
            $db_bodega = new ConnectionHandler($this->ruta_raiz, "bodega");
            $rs_archivo = $db_bodega->query("select func_grabar_archivo(E'".$this->registro["radi_nume_temp"].".pdf', E'$pdf') as arch_codi");
            
            if ((0+$rs_archivo->fields["ARCH_CODI"]) != 0) {
                $this->path_pdf = (0+$rs_archivo->fields["ARCH_CODI"]);
                $sql = "update radicado set arch_codi=".$this->path_pdf." where coalesce(radi_path,'')='' and arch_codi=0 and esta_codi in (0,2,5,6)
                        and radi_nume_temp=" . $this->registro["radi_nume_temp"];
                $this->db->conn->Execute($sql);
            }
        }
        return;
    }

    /***************************************************************************
    ** Limpia el cuerpo del documento de tags que no son soportados por el    **
    ** módulo html_a_pdf o que no deberían estar                              **
    ***************************************************************************/
    function limpiar_cuerpo_documento($texto) {
        $origen  = array("<br","<input","<meta","<style","<title","<span","<font","<div","<link","<p","<a","<tr","<td","<hr","<table","<li");
        $destino = array("<br","<input","<meta","<style","<title","<span","<font","<div","<link","<p","<a","<tr","<td","<hr","<table","<li");
        $origen[] = "text-align:left"; $destino[] = "text-align: justify";
        $origen[] = "text-align: left"; $destino[] = "text-align: justify";
        $origen[] = "</span>";  $destino[] = "";
//        $origen[] = "</div>";   $destino[] = "";
        $origen[] = "</p>";     $destino[] = "<br>";
        $origen[] = "</a>";     $destino[] = "";
        $origen[] = "<pre>";    $destino[] = "";
        $origen[] = "</pre>";   $destino[] = "";
        $origen[] = "</col>";   $destino[] = "";
        $origen[] = "<tt>";     $destino[] = "";
        $origen[] = "</tt>";    $destino[] = "";
        $texto = str_ireplace($origen,$destino,$texto);
        $texto = preg_replace(':<input.*?>.*?</input>:is', '', $texto);
        $texto = preg_replace(':<input.*?>:is', '', $texto);
/*        $texto = preg_replace(':<meta.*?>.*?</meta>:is', '', $texto);
        $texto = preg_replace(':<meta.*?>:i', '', $texto);/**/
        $texto = preg_replace(':<style.*?>.*?</style>:is', '', $texto);
        $texto = preg_replace(':<style.*?>:is', '', $texto);
        $texto = preg_replace(':<title.*?>.*?</title>:is', '', $texto);
        $texto = preg_replace(':<span.*?>:is', '', $texto);
        $texto = preg_replace(':<pre .*?>:is', '', $texto);
        $texto = preg_replace(':<p .*?>:is', '', $texto);
        $texto = preg_replace(':<a .*?>:is', '', $texto);
/*        $texto = preg_replace(':<table .*?>:is', '<table border="1" cellpading="1" cellspacing="1">', $texto); 
        $texto = preg_replace(':<tr .*?>:is', '<tr>', $texto);
        $texto = preg_replace(':<hr.*?>:is', '<hr align="center" valign="middle">', $texto);
        $texto = preg_replace(':<td.*?>:is', '<td align="justify" valign="top">', $texto); /* */
        $texto = preg_replace(':<li .*?>:is', '<li align="justify">', $texto);
        $texto = preg_replace(':<!--.*?-->:is', '', $texto);
        $texto = preg_replace(':<col .*?>:is', '', $texto); 
        $texto = preg_replace(':<br.*?>:is', '&nbsp;<br>', $texto);
        $texto = str_replace("′", "&prime;", $texto);
        return $texto;
    }

    /*****************************************************************************
    ** Formatea los textos eliminando caracteres especiales, tildes, saltos de  **
    ** línea y los deja en formato HTML, ademas los transforma a upper, lower   **
    ** o intercala entre mayúsculas y minúsculas                                **
    *****************************************************************************/
    function formatear_datos_documento($texto, $case="") {
        $mayusculas = array ("Á", "É", "Í", "Ó", "Ú", "À", "È", "Ì", "Ò", "Ù", "Ä", "Ë", "Ï", "Ö", "Ü", "Â", "Ê", "Î", "Ô", "Û", "Ã", "Õ", "Ñ", "Ç");
        $minusculas = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü", "â", "ê", "î", "ô", "û", "ã", "õ", "ñ", "ç");
        if ($case == "U") { // Pasar a mayúsculas
            $texto = str_replace($minusculas, $mayusculas, strtoupper($texto));
        } elseif ($case == "L") { //Minúsculas
            $texto = str_replace($mayusculas, $minusculas, strtolower($texto));
        } elseif ($case == "I") { //Intercalar
            // Por versiones anteriores en las que todo el texto podía estar todo en mayúsculas o minúsculas
            if ($texto == strtoupper($texto) or $texto == strtolower($texto)) {
                $texto = str_replace($mayusculas, $minusculas, strtolower($texto));
                $cadena = "";
                foreach (explode(" ", $texto) as $palabra) {
                    if (strlen($palabra)<=3)
                        $cadena .= " ".$palabra;
                    else {
                        if (ltrim($palabra,"áéíóúàèìòùäëïöüâêîôûãõñ") == $palabra)
                            $cadena .= " ".strtoupper(substr($palabra,0,1)).substr($palabra,1);
                        else // en caso que comience por un caracter especial
                            $cadena .= " ".strtoupper(str_replace($minusculas, $mayusculas, substr($palabra,0,2))).substr($palabra,2);
                    }
                }
                $origen  = array("Ff.Aa.");
                $destino = array("FF.AA.");
                $texto = str_ireplace($origen, $destino, $cadena);
            }
        }
        // Eliminamos posibles tags html
        $origen  = array("&amp;","&quot;","<"   ,">");
        $destino = array("&amp;","&quot;","&lt;","&gt;");
        $texto = str_ireplace($origen, $destino, $texto);
        // Cambiamos letras con tildes a formato html
        $origen  = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü"
                        , "â", "ê", "î", "ô", "û", "ã", "õ", "ñ"
                        , "Á", "É", "Í", "Ó", "Ú", "À", "È", "Ì", "Ò", "Ù", "Ä", "Ë", "Ï", "Ö", "Ü"
                        , "Â", "Ê", "Î", "Ô", "Û", "Ã", "Õ", "Ñ"
                        , "ç", "Ç", "°", "º", "ª", "½", "¿", "·", "~"
                        , "©", "®", "™");
        $destino = array ("&aacute;", "&eacute;", "&iacute;", "&oacute;", "&uacute;"
                        , "&agrave;", "&egrave;", "&igrave;", "&ograve;", "&ugrave;"
                        , "&auml;", "&euml;", "&iuml;", "&ouml;", "&uuml;"
                        , "&acirc;", "&ecirc;", "&icirc;", "&ocirc;", "&ucirc;"
                        , "&atilde;", "&otilde;", "&ntilde;"
                        , "&Aacute;", "&Eacute;", "&Iacute;", "&Oacute;", "&Uacute;"
                        , "&Agrave;", "&Egrave;", "&Igrave;", "&Ograve;", "&Ugrave;"
                        , "&Auml;", "&Euml;", "&Iuml;", "&Ouml;", "&Uuml;"
                        , "&Acirc;", "&Ecirc;", "&Icirc;", "&Ocirc;", "&Ucirc;"
                        , "&Atilde;", "&Otilde;", "&Ntilde;"
                        , "&ccedil;", "&Ccedil;", "&deg;", "&ordm;", "&ordf;", "&frac12;", "&iquest;", "&middot;", "&sim;"
                        , "&copy;", "&reg;", "&trade;");
        $texto = str_ireplace($origen, $destino, $texto);
        //Aumento saltos de línea y espacios dobles en el texto
        $origen  = array("\\n" ,"  ","&lt;br&gt;", "&amp;amp;", "&amp;quot;", "′"      );
        $destino = array("<br>"," " , "<br>"     , "&amp;"    , "&quot;"    , "&prime;");
        $texto = str_ireplace($origen, $destino, $texto);

        return trim($texto);
    }

}
?>
