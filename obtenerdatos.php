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
*
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	Santiago Cordovilla	SC			19-12-2008
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			01-12-2008
**/

/**
* Obtener informacion de un radicado o documento en funcion del numero (clave primaria).
* @param string campo a consultar
* @param string numero de radicado (clave primaria)
* @param object objeto para conexion a la base de datos
* @return string 
**/
    function ObtenerCampoRadicado($campo,$radicado,$db)
    {//obtiene el texto del radicado en función del número
        if (trim($campo)=="" or trim($radicado)=="") return "";
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isqlTEXT = "select $campo as dato from RADICADO where RADI_NUME_RADI=".trim($radicado);
        $rs=$db->query($isqlTEXT);
        //echo "<hr> $isqlTEXT <br>".$rs->fields["DATO"];
        return $rs->fields["DATO"];
    }

/**
* Obtener el numero de un radicado o documento en funcion del texto (radi_nume_text).
* @param string texto de radicado (radi_nume_text)
* @param object objeto para conexion a la base de datos
* @return string (clave primaria)
**/
    function ObtenerNumeroRadicado($radicado,$db)
    {//Obtiene el número de radicado en función del texto
        if (trim($radicado)=="") return "";
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isqlTEXT = "select radi_nume_radi
                     from RADICADO where trim(upper(radi_nume_text)) LIKE '".strtoupper(trim($radicado))."'";
        $rs=$db->query($isqlTEXT);
        return $rs->fields["RADI_NUME_RADI"];
    }

/**
* Obtener opciones de impresion del un documento.
**/
    function ObtenerDatosOpcImpresion($radicado,$db){
        if (trim($radicado)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isql="select * from opciones_impresion where radi_nume_radi=".trim($radicado)." order by opc_imp_codi desc" ;
        $rs=$db->query($isql);

        $vector["OPC_IMP_CODI"] = $rs->fields["OPC_IMP_CODI"];
        $vector["RADI_NUME_RADI"] = $rs->fields["RADI_NUME_RADI"];
        $vector["OCULTAR_NUME_RADI"] = $rs->fields["OPC_IMP_OCULTAR_NUME_RADI"];
        $vector["MOSTRAR_PARA"] = $rs->fields["OPC_IMP_MOSTRAR_PARA"];
        $vector["JUSTIFICAR_FIRMA"] = $rs->fields["OPC_IMP_JUSTIFICAR_FIRMA"];
        $vector["TITULO_NATURAL"] = $rs->fields["OPC_IMP_TITULO_NATURAL"];
        $vector["EXT_INSTITUCION"] = $rs->fields["OPC_IMP_EXT_INSTITUCION"];
        $vector["DESTINO_DESTINATARIO"] = $rs->fields["OPC_IMP_DESTINO_DESTINATARIO"];
        $vector["FRASE_REMITENTE"]= $rs->fields["OPC_IMP_FRASE_REMITENTE"];
        $vector["FRASE_DESPEDIDA"]= $rs->fields["OPC_IMP_DESPEDIDA"];
        $vector["DIRECCION"]= $rs->fields["OPC_IMP_DIRECCION"];
        $vector["CIUDAD"]= $rs->fields["OPC_IMP_CIUDAD"];
        $vector["TELEFONO"]= $rs->fields["OPC_IMP_TELEFONO"];
        $vector["FIRMANTES"] = $rs->fields["OPC_IMP_FIRMANTES"];
        $vector["OCULTA_FRASE_REMITENTE"] = $rs->fields["OPC_IMP_OCULTAR_FRASE_REM"];
        $vector["CARGO_CABECERA"] = $rs->fields["OPC_IMP_CARGO_CABECERA"];
        $vector["TEXTO_SOBRE"] = $rs->fields["OPC_IMP_TEXTO_SOBRE"];
        $vector["OPC_IMP_TIPO_NOTA"] = $rs->fields["OPC_IMP_TIPO_NOTA"];
        $vector["OPC_JUSTIFICAR_FECHA"] = $rs->fields["OPC_IMP_JUSTIFICAR_FECHA"];
        $vector["OCULTAR_ASUNTO"] = $rs->fields["OPC_IMP_OCULTAR_ASUNTO"];
        $vector["LETRA_ITALICA"] = $rs->fields["OPC_IMP_LETRA_ITALICA"];
        $vector["OCULTAR_ATENTAMENTE"] = $rs->fields["OPC_IMP_OCULTAR_ATENTAMENTE"];
        $vector["OCULTAR_ANEXO"] = $rs->fields["OPC_IMP_OCULTAR_ANEXO"];
        $vector["OCULTAR_REFERENCIA"] = $rs->fields["OPC_IMP_OCULTAR_REFERENCIA"];
        $vector["OCULTAR_SUMILLAS"] = $rs->fields["OPC_IMP_OCULTAR_SUMILLAS"];
        $vector["CIUDAD_DADO_EN"] = $rs->fields["OPC_IMP_CIUDAD_DADO_EN"];
        
        return $vector;
    }

/**
* Obtener opciones de impresion del un documento para sobres.
**/
function ObtenerDatosOpcImpresionSobre($radiNumeRadi,$usuaCodi,$db){
        if (trim($opcImpCodi)=="" and trim($usuaCodi)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isql="select *, c.nombre as opc_imp_sob_ciudad
                from opciones_impresion_sobre left outer join ciudad c on id = opc_imp_sob_ciudad
                where radi_nume_radi=".trim($radiNumeRadi)." and usua_codi = ".trim($usuaCodi) ;

        $rs=$db->query($isql);

        $vector["OPC_IMP_SOB_CODI"] = $rs->fields["OPC_IMP_SOB_CODI"];
        $vector["DIRECCION"] = $rs->fields["OPC_IMP_SOB_DIRECCION"];
        $vector["CIUDAD"] = $rs->fields["OPC_IMP_SOB_CIUDAD"];
        $vector["TELEFONO"] = $rs->fields["OPC_IMP_SOB_TELEFONO"];

        return $vector;
    }

/**
* Obtener datos de un radicado o documento en funcion del numero (clave primaria).
* @param string numero de radicado (clave primaria)
* @param object objeto para conexion a la base de datos
* @return array() retorna los datos obtenidos del radicado.
**/

    function ObtenerDatosRadicado($radicado,$db)
    {//Obtiene datos del radicado en función del texto
        if (trim($radicado)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isqlTEXT = "select r.*, e.esta_desc, tr.trad_descr,
                     radi_fech_asig::date - CURRENT_DATE as num_dias
                     from radicado r left outer join estado e on r.esta_codi=e.esta_codi
                     left outer join tiporad tr on r.radi_tipo = tr.trad_codigo
                     where r.RADI_NUME_RADI=".trim($radicado);
        $rs=$db->query($isqlTEXT);
        $vector["radi_nume_radi"] = $rs->fields["RADI_NUME_RADI"];
        $vector["radi_nume_temp"] = $rs->fields["RADI_NUME_TEMP"];
        $vector["radi_nume_text"] = $rs->fields["RADI_NUME_TEXT"];
        $vector["radi_nume_asoc"] = $rs->fields["RADI_NUME_ASOC"];
//        $vector["radi_text_temp"] = $rs->fields["RADI_TEXT_TEMP"];
        $vector["usua_rem"] = $rs->fields["RADI_USUA_REM"];
        $vector["usua_dest"] = $rs->fields["RADI_USUA_DEST"];
        $vector["inst_actu"] =$rs->fields["RADI_INST_ACTU"];
        $vector["usua_actu"] =$rs->fields["RADI_USUA_ACTU"];
        $vector["usua_radi"] =$rs->fields["RADI_USUA_RADI"];
        $vector["radi_fecha"] =$rs->fields["RADI_FECH_OFIC"];
        $vector["fecha_tramite"] =$rs->fields["RADI_FECH_ASIG"];
        $vector["fecha_firma"] =$rs->fields["RADI_FECH_FIRMA"];
        $vector["usua_firma"] =$rs->fields["RADI_NOMB_USUA_FIRMA"];
        $vector["dias_tramite"] =$rs->fields["NUM_DIAS"];
        $vector["radi_tipo"] =$rs->fields["RADI_TIPO"];
        $vector["radi_tipo_desc"] =$rs->fields["TRAD_DESCR"];
        $vector["radi_path"] =$rs->fields["RADI_PATH"];
        $vector["radi_resumen"] = stripcslashes($rs->fields["RADI_RESUMEN"]);
        $vector["radi_codi_texto"] = $rs->fields["RADI_TEXTO"];
        $vector["radi_asunto"] = stripcslashes($rs->fields["RADI_ASUNTO"]);
        $vector["estado"] =$rs->fields["ESTA_CODI"];
        $vector["desc_estado"] =$rs->fields["ESTA_DESC"];
        $vector["radi_padre"] =$rs->fields["RADI_NUME_DERI"];
        $vector["radi_referencia"] =$rs->fields["RADI_CUENTAI"];
        $vector["cca"] =$rs->fields["RADI_CCA"];
        $vector["radi_desc_anexos"] =$rs->fields["RADI_DESC_ANEX"];
        $vector["seguridad"] =$rs->fields["RADI_PERMISO"];
        $vector["usar_plantilla"] =$rs->fields["USAR_PLANTILLA"];
        $vector["ajust_texto"] =$rs->fields["AJUST_TEXTO"];
        $vector["radi_tipo_impresion"] =$rs->fields["RADI_TIPO_IMPRESION"];
        $vector["cod_codi"] =$rs->fields["COD_CODI"];
        $vector["cat_codi"] =$rs->fields["CAT_CODI"];
        $vector["radi_lista_dest"] =$rs->fields["RADI_LISTA_DEST"];
        $vector["fecha_radicado"] =$rs->fields["RADI_FECH_RADI"];
        //$vector["radi_anexo"] =$rs->fields["RADI_ANEXO"];
        $vector["flag_archivo_asociado"] =$rs->fields["RADI_TIPO_ARCHIVO"];
        $vector["ocultar_recorrido"] =$rs->fields["RADI_OCULTAR_RECORRIDO"];
        $vector["usua_redirigido"] =$rs->fields["RADI_USUA_REDIRIGIDO"];
        $vector["arch_codi"] =$rs->fields["ARCH_CODI"];
        $vector["arch_codi_firma"] =$rs->fields["ARCH_CODI_FIRMA"];
        $vector["radi_imagen"] =$rs->fields["RADI_IMAGEN"];

        /*
        $isqlTEXT = "select text_texto from radi_texto where text_codi=".$vector["radi_codi_texto"];
        $rs=$db->query($isqlTEXT);
        $vector["radi_texto"] = stripcslashes($rs->fields["TEXT_TEXTO"]);
        /* */
        //var_dump($rs);
        //echo "<hr> $isqlTEXT <br> TEXTRAD=".$vector["textrad"];
        return $vector;
    }

/**
* Obtener datos de usuario dependiendo si es usuario interno o ciudadano.
* @param string codigo de usuario login, cedula o codigo
* @param object objeto para conexion a la base de datos
* @param string lo que se envio "L" login o "C" cedula.
* @param string tipo de usuario usuario interno o ciudadano
* @return string (clave primaria)
**/
    function ObtenerDatosUsuario($codigo,$db,$campo="U", $tipo="")
    {//Obtiene datos del usuario en función del texto
        if (trim($codigo)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        /*	$isqlTEXT = "select *, (CASE WHEN inst_codi=0 THEN 3 ELSE (CASE WHEN inst_codi=".$_SESSION["inst_codi"]." THEN 1 ELSE 2 END) END) as \"usua_tipo\" from usuario where ";*/
        $isqlTEXT = "select * from usuario where ";

        switch($campo) {
            case 'L':
                $isqlTEXT .= " upper(usua_login) like '" . strtoupper(trim($codigo)) . "'";
                break;
            case 'C':
                $isqlTEXT .= " usua_cedula like '" . trim(str_replace("'","",$codigo)) . "'";
                break;
            default:
                $isqlTEXT .= " usua_codi=".$codigo;
                break;
        }
        switch($tipo) {
            case 'U':
                $isqlTEXT .= " and tipo_usuario=1";
                break;
            case 'C':
                $isqlTEXT .= " and tipo_usuario=2";
                break;
        }
        
        $rs=$db->query($isqlTEXT);

        $vector["usua_codi"] =trim($rs->fields["USUA_CODI"]);
        $vector["cedula"] =trim($rs->fields["USUA_CEDULA"]);
        $vector["login"] =trim($rs->fields["USUA_LOGIN"]);
        $vector["nombre"] = $rs->fields["USUA_NOMBRE"];
        $vector["usua_nombre"] =$rs->fields["USUA_NOMB"];
        $vector["usua_apellido"] =$rs->fields["USUA_APELLIDO"];
        $vector["inst_codi"] =trim($rs->fields["INST_CODI"]);
        $vector["institucion"] =$rs->fields["INST_NOMBRE"];
        $vector["inst_estado"] =trim($rs->fields["INST_ESTADO"]);
        $vector["usua_estado"] =trim($rs->fields["USUA_ESTA"]);
        $vector["cargo"] = $rs->fields["USUA_CARGO"];
        $vector["cargo_cabecera"] = $rs->fields["USUA_CARGO_CABECERA"];
        $vector["titulo"] =$rs->fields["USUA_TITULO"];
        $vector["abr_titulo"] =$rs->fields["USUA_ABR_TITULO"];
        $vector["email"] =$rs->fields["USUA_EMAIL"];
        $vector["dependencia"] =$rs->fields["DEPE_NOMB"];
        $vector["depe_codi"] =$rs->fields["DEPE_CODI"];
        $vector["tipo_usuario"] =$rs->fields["TIPO_USUARIO"];
        $vector["direccion"] =$rs->fields["USUA_DIRECCION"];
        $vector["telefono"] =$rs->fields["USUA_TELEFONO"];
        $vector["ciudad"] =$rs->fields["USUA_CIUDAD"];
        $vector["usua_firma_path"] =$rs->fields["USUA_FIRMA_PATH"];
        $vector["tipo_certificado"] =$rs->fields["USUA_TIPO_CERTIFICADO"];
        $vector["cargo_tipo"] =$rs->fields["CARGO_TIPO"];
        
        if($vector["cargo_tipo"] == 1)
            $perfil = "Jefe";
        else
            $perfil = "Normal";
        $vector["perfil"] = $perfil;

        //echo "<br>$isqlTEXT<br>";//.$vector["usua_tipo"];
        //var_dump($vector);
        return $vector;
    }


    function ObtenerPermisoUsuario($codigo, $permiso, $db)
    {//Verifica si un usuario tiene un determinado permiso
        if (trim($codigo)=="" or trim($permiso)=="") return 0;

        $sql = "select * from permiso_usuario where usua_codi=$codigo and id_permiso=$permiso";
        $rs=$db->query($sql);

        if ($rs->EOF) {
            $sql = "select ciu_codigo from ciudadano where ciu_codigo=$codigo";
            $rs=$db->query($sql);
            if (!$rs->EOF) return 1;
            return 0;
        }
        return 1;

    }


/**
* Obtener datos de la dependencia dependiendo del numero (clave primaria).
* @param string codigo de la dependencia (clave primaria)
* @param object objeto para conexion a la base de datos
* @return array() datos de la dependencia
**/
    function ObtenerDatosDependencia($dependencia,$db)
    { //Obtiene datos de la area en función del texto
        if (trim($dependencia)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isqlTEXT = "select d.depe_codi, d.depe_nomb, d.dep_sigla, coalesce(d.depe_plantilla,d.depe_codi) as \"depe_plantilla\"
                         , d.depe_pie1, c.nombre, depe_codi_padre, dep_central
                    from dependencia d left outer join ciudad c on d.depe_pie1=c.id::text
                    where d.depe_codi=$dependencia";
        $rs=$db->query($isqlTEXT);
        $vector["codigo"] = $rs->fields["DEPE_CODI"];
        $vector["nombre"] = $rs->fields["DEPE_NOMB"];
        $vector["sigla"] = $rs->fields["DEP_SIGLA"];
        $vector["id_ciudad"] = $rs->fields["DEPE_PIE1"];//obtiene el id de la ciudad del area editada
        $vector["ciudad"] = $rs->fields["NOMBRE"];//obtiene el nombre de la ciudad del area editada
        $vector["plantilla"] = $rs->fields["DEPE_PLANTILLA"];
        $vector["padre"] = $rs->fields["DEPE_CODI_PADRE"];	//Codigo del area padre
        $vector["archivo"] = $rs->fields["DEP_CENTRAL"];	//Codigo del area que es archivo central
        return $vector;
    }

/**
* Obtener datos de la institucion dependiendo del codigo (clave primaria).
* @param string codigo de la institucion (clave primaria)
* @param object objeto para conexion a la base de datos
* @return array() datos de la institucion
**/
    function ObtenerDatosInstitucion($codigo,$db)
    {
        if (trim($codigo)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

        $isqlTEXT = "select * from institucion where inst_codi=".$codigo;
        $rs=$db->query($isqlTEXT);
        $vector["codigo"] = $rs->fields["INST_CODI"];
        $vector["ruc"] = $rs->fields["INST_RUC"];
        $vector["nombre"] = $rs->fields["INST_NOMBRE"];
        $vector["sigla"] = $rs->fields["INST_SIGLA"];
        $vector["estado"] = $rs->fields["INST_ESTADO"];
        $vector["logo"] = $rs->fields["INST_LOGO"];
        $vector["telefono"] = $rs->fields["INST_TELEFONO"];
        $vector["coordinador"] = $rs->fields["INST_COORDINADOR"];
        $vector["fraseDespedida"] = $rs->fields["INST_DESPEDIDA_OFI"];
        return $vector;
    }

/**
 * Obtener los datos de una lista
 * @param string codigo de la lista (clave primaria)
 * @param object objeto para conexion a base de datos
 * @return array() datos de la lista
 **/
    function ObtenerDatosLista($lst_codigo,$db){
        if (trim($lst_codigo)=="") return array();
        $sql = "select * from lista where lista_codi=$lst_codigo";
        $rs = $db->query($sql);
        $lst['codigo'] = $rs->fields["LISTA_CODI"];
        $lst['nombre']      = $rs->fields["LISTA_NOMBRE"];
        $lst['descripcion'] = $rs->fields["LISTA_DESCRIPCION"];
        $lst['fecha'] = $rs->fields["LISTA_FECHA"];
        $lst['institucion'] = $rs->fields["INST_CODI"];
        $lst['usuario'] = $rs->fields["USUA_CODIGO"];
        return $lst;
    }

/**
 * Obtener ultima fecha de actualizacion del documento
 **/
    function ObtenerUltimaFecha($radi_nume_radi, $transaccion ,$db){
        if (trim($radi_nume_radi)=="" or trim($transaccion)=="") return "";
        $sql = "select max(hist_fech) as fecha from hist_eventos where radi_nume_radi = "
                . $radi_nume_radi . " and sgd_ttr_codigo = " . $transaccion;
                //echo $sql;
        $rs = $db->query($sql);
        $fecha = $rs->fields["FECHA"];
        return $fecha;
    }

/**
 * Obtener informacion de la ciudad por usuario
 **/
    function ObtenerCiudadUsua($tabla, $condicion="", $db){
        if (trim($tabla)=="") return array();
        $sql = "select id, nombre from $tabla";
        if(trim($condicion)!='')
            $sql.= " where $condicion";
        //echo $sql;
        $rs=$db->query($sql);
        $ciudad['id'] = $rs->fields["ID"];
        $ciudad['nombre'] = $rs->fields["NOMBRE"];
        return $ciudad;
    }

/**
 * Obtener datos de categoria
 */
    function ObtenerCategoria($cat_codi, $db){
        if (trim($cat_codi)=="") return array();
        if($cat_codi=="")
            $cat_codi = 0;
        $sql = "select cat_codi, cat_descr from categoria where cat_codi = $cat_codi";
        $rs=$db->query($sql);
        $vector['id'] = $rs->fields["CAT_CODI"];
        $vector['nombre'] = $rs->fields["CAT_DESCR"];
        return $vector;
    }

/**
 * Obtener datos de tipificacion (codificacion)
 */
    function ObtenerTipificacion($inst_codi, $cod_codi, $db){
        if (trim($inst_codi)=="") return array();
        if($cod_codi=="")
            $cod_codi = 0;
        $sql = "select cod_codi, cod_descripcion from codificacion where inst_codi = $inst_codi and cod_codi = $cod_codi";
        $rs=$db->query($sql);
        $vector['id'] = $rs->fields["COD_CODI"];
        $vector['nombre'] = $rs->fields["COD_DESCRIPCION"];
        return $vector;
    }

/**
 * Obtener datos de jefe ó asitente de area
 */
    function ObtenerJefeArea($inst_codi, $depe_codi, $cargo_tipo, $db){
        if (trim($inst_codi)=="" or trim($depe_codi)=="") return array();
        $sql = "select usua_codi, ciu_codi from usuarios where inst_codi = $inst_codi and depe_codi = $depe_codi and cargo_tipo = $cargo_tipo";
        //echo "<br>".$sql;
        $rs=$db->query($sql);
        $codigoUsu = $rs->fields["USUA_CODI"];
        $vector = ObtenerDatosUsuario($codigoUsu,$db);
        $vector['ciudad'] = $rs->fields["CIU_CODI"];
        return $vector;
    }

    /** Obtener firma digitalizada de usuario**/

    function ObtenerFirma($usua_codi, $db){
        $sqlFirma = 'select * from firma_digitalizada where usua_codi = '. $usua_codi;
        $rs=$db->query($sqlFirma);
        $vector['FIR_DIR_CODI'] = $rs->fields["FIR_DIR_CODI"];
        $vector["USUA_CODI"] = $rs->fields["USUA_CODI"];
        $vector["FIR_DIG_CUERPO"] = $rs->fields["FIR_DIG_CUERPO"];
        $vector["FIR_DIG_EXT"] = $rs->fields["FIR_DIG_EXT"];
        return $vector;       
    }

    /**Obtener los radicados que se encuentran en estado 5**/
    function ObtenerRadicadoEstado5($radi_nume_padre, $db){
        
        $where = " radi_nume_temp = $radi_nume_padre and esta_codi = 5 ";

        $sqlContar = "select count(*) as num from radicado where $where";
        $rsContar = $db->query($sqlContar);
        $radicado = 0;
        if($rsContar->fields["NUM"] != 0)
        {
            $sql = "select radi_nume_radi, radi_path from radicado where $where";
            $rsRad = $db->query($sql);
            unlink($rsRad->fields["RADI_PATH"]);
            $sqlActualizar = "update radicado set radi_path = null where $where";
            $rs = $db->query($sqlActualizar);
            $radicado = $rsRad->fields["RADI_NUME_RADI"];
        }
        return $radicado;
    }

    function ObtenerObservacionCiudadano($usua_codi, $datosArray, $db){
        $rsCiudadano = ObtenerDatosUsuario($usua_codi, $db);

        if("E'".trim($rsCiudadano["cedula"])."'" != trim($datosArray["CIU_CEDULA"])) //Cedula
            $observacion = "Cédula de ".$rsCiudadano["cedula"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_CEDULA"])).'<br>';

        if("E'".trim($rsCiudadano["usua_nombre"])."'" != trim($datosArray["CIU_NOMBRE"])) //Nombre
            $observacion .= "Nombre de ".$rsCiudadano["usua_nombre"]." a ".str_replace("'","",str_replace("initcap(E'","",$datosArray["CIU_NOMBRE"])).'<br>';

        if("E'".trim($rsCiudadano["usua_apellido"])."'" != trim($datosArray["CIU_APELLIDO"])) //Apellido
            $observacion .= "Apellido de ".$rsCiudadano["usua_apellido"]." a ".str_replace("'","",str_replace("initcap(E'","",$datosArray["CIU_APELLIDO"])).'<br>';

        if("E'".trim($rsCiudadano["titulo"])."'" != trim($datosArray["CIU_TITULO"])) //Titulo
            $observacion .= "Título de ".$rsCiudadano["titulo"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_TITULO"])).'<br>';

        if("E'".trim($rsCiudadano["abr_titulo"])."'" != trim($datosArray["CIU_ABR_TITULO"])) //Abrev. de titulo
            $observacion .= "Abrv.Título de ".$rsCiudadano["abr_titulo"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_ABR_TITULO"])).'<br>';

        if("E'".trim($rsCiudadano["institucion"])."'" != trim($datosArray["CIU_EMPRESA"])) //Empresa
            $observacion .= "Empresa de ".$rsCiudadano["institucion"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_EMPRESA"])).'<br>';

        if("E'".trim($rsCiudadano["cargo"])."'" != trim($datosArray["CIU_CARGO"])) //Cargo
            $observacion .= "Cargo de ".$rsCiudadano["cargo"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_CARGO"])).'<br>';

        if("E'".trim($rsCiudadano["direccion"])."'" != trim($datosArray["CIU_DIRECCION"])) //Direccion
            $observacion .= "Dirección de ".$rsCiudadano["direccion"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_DIRECCION"])).'<br>';

        if("E'".trim($rsCiudadano["email"])."'" != trim($datosArray["CIU_EMAIL"])) //E-mail
            $observacion .= "E-mail de ".$rsCiudadano["email"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_EMAIL"])).'<br>';

        if("E'".trim($rsCiudadano["telefono"])."'" != trim($datosArray["CIU_TELEFONO"])) //Telefono
            $observacion .= "Telefono de ".$rsCiudadano["telefono"]." a ".str_replace("'","",str_replace("E'","",$datosArray["CIU_TELEFONO"])).'<br>';

        if($datosArray["CIUDAD_CODI"]) //Ciudad
        {
            $condicion = " id = ".$datosArray["CIUDAD_CODI"]." ";
            $rsCiudad = ObtenerCiudadUsua(' ciudad ', $condicion, $db);
            if(trim($rsCiudadano["ciudad"]) != $rsCiudad['nombre'])
                $observacion .= "Ciudad de ".$rsCiudadano["ciudad"]." a ".$rsCiudad['nombre'].'<br>';
        }
        
        if($datosArray["CIU_ESTADO"])
            if(trim($rsCiudadano["usua_estado"]) != trim($datosArray["CIU_ESTADO"])) //Ciudadano estado
            {
                if($rsCiudadano["usua_estado"]==1)
                    $estado = "Activo";
                else
                    $estado = "Inactivo";

                if(trim($datosArray["CIU_ESTADO"])==1)
                    $ciuEstado = "Activo";
                else
                    $ciuEstado = "Inactivo";
                $observacion .= "Ciudadano estado de ".$estado." a ".$ciuEstado.'<br>';
            }

        return $observacion;
    }

    function ObtenerObservacionFuncionario($usua_codi, $datosArray, $db){
        $rsUsuario = ObtenerDatosUsuario($usua_codi, $db);
        
        if("E'".trim($rsUsuario["cedula"])."'" != trim($datosArray["USUA_CEDULA"])) //Cedula
            $observacion = "Cédula de ".$rsUsuario["cedula"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_CEDULA"])).'<br>';

        if("E'".trim($rsUsuario["usua_nombre"])."')" != trim($datosArray["USUA_NOMB"])) //Nombre
            $observacion .= "Nombre de ".$rsUsuario["usua_nombre"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_NOMB"])).'<br>';

        if("E'".trim($rsUsuario["usua_apellido"])."')" != trim($datosArray["USUA_APELLIDO"])) //Apellido
            $observacion .= "Apellido de ".$rsUsuario["usua_apellido"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_APELLIDO"])).'<br>';

        if($rsUsuario["depe_codi"] != $datosArray["DEPE_CODI"]) //Area o dependencia
        {
            $rsDependencia = ObtenerDatosDependencia($datosArray["DEPE_CODI"],$db);
            $observacion .= "Área o dependencia de ".$rsUsuario["dependencia"]." a ".$rsDependencia['nombre'].'<br>';
        }

        if("E'".trim($rsUsuario["titulo"])."'" != trim($datosArray["USUA_TITULO"])) //Titulo
            $observacion .= "Título de ".$rsUsuario["titulo"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_TITULO"])).'<br>';

        if("E'".trim($rsUsuario["abr_titulo"])."'" != trim($datosArray["USUA_ABR_TITULO"])) //Abrev. de titulo
            $observacion .= "Abrv.Título de ".$rsUsuario["abr_titulo"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_ABR_TITULO"])).'<br>';

        if($datosArray["USUA_CARGO"])
            $cargoUsua = str_replace("'","",str_replace("E'","",$datosArray["USUA_CARGO"]));
        
        if(trim($rsUsuario["cargo"]) != ucwords($cargoUsua)) //Puesto
            $observacion .= "Puesto de ".$rsUsuario["cargo"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_CARGO"])).'<br>';

        if("E'".trim($rsUsuario["direccion"])."'" != trim($datosArray["USUA_DIRECCION"])) //Direccion
            $observacion .= "Dirección de ".$rsUsuario["direccion"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_DIRECCION"])).'<br>';

        if("E'".trim($rsUsuario["email"])."'" != trim($datosArray["USUA_EMAIL"])) //E-mail
            $observacion .= "E-mail de ".$rsUsuario["email"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_EMAIL"])).'<br>';

        if("E'".trim($rsUsuario["telefono"])."'" != trim($datosArray["USUA_TELEFONO"])) //Telefono
            $observacion .= "Telefono de ".$rsUsuario["telefono"]." a ".str_replace("'","",str_replace("E'","",$datosArray["USUA_TELEFONO"])).'<br>';

        if(trim($datosArray["CIU_CODI"])) //Ciudad
        {
            $condicion = ' id = '.trim($datosArray["CIU_CODI"]);
            $rsCiudad = ObtenerCiudadUsua('ciudad', $condicion, $db);
            if(trim($rsUsuario["ciudad"]) != $rsCiudad['nombre'])
                $observacion .= "Ciudad de ".$rsUsuario["ciudad"]." a ".$rsCiudad['nombre'].'<br>';
        }

        if(trim($rsUsuario["usua_estado"]) != trim($datosArray["USUA_ESTA"])) //Usuario activado o desactivado
        {
            if($rsUsuario["usua_estado"]==1)
                $estado = "Activo";
            else
                $estado = "Inactivo";

            if(trim($datosArray["USUA_ESTA"])==1)
                $usuaEstado = "Activo";
            else
                $usuaEstado = "Inactivo";
            $observacion .= "Usuario estado de ".$estado." a ".$usuaEstado.'<br>';
        }

        if(trim($observacion) == ""){
            $observacion = "Modificación de Perfil, Cargo, Observaciones o Permisos en el funcionario.<br>";
        }

        return $observacion;
    }

    /*
     * Obtener la fecha maxima que la tarea puede tener
     */
    function obtenerFechaMaximaTarea($db, $listaRadicados, $codUsario){
        //Fecha maxima que puede tener la tarea
        $fechaMaximaTarea = date("Y-m-d");

        //Consulta si el documento tiene una tarea padre para tomar como fecha maxima de tarea la fecha de la tarea padre.
        $sqlFechaTarea = "select substr(min(fecha_maxima::text),1,10) as fecha_maxima from tarea where radi_nume_radi in ($listaRadicados) and estado=1 and usua_codi_dest=$codUsario";
        $rsFechaTarea = $db->query($sqlFechaTarea);
        if($rsFechaTarea->fields["FECHA_MAXIMA"])
            $fechaMaximaTarea = $rsFechaTarea->fields["FECHA_MAXIMA"];
        return $fechaMaximaTarea;
    }
    /*
     * Obtener la fecha maxima que la tarea puede tener para tareas nuevas
     */
    function obtenerFechaMaximaTareaNueva($db, $listaRadicados, $codUsario){
        //Fecha maxima que puede tener la tarea
        $fechaMaximaTarea = date("Y-m-d");

        //Consulta si el documento tiene una tarea padre para tomar como fecha maxima de tarea la fecha de la tarea padre.
        $sqlFechaTarea = "select substr(min(fecha_maxima::text),1,10) as fecha_maxima from tarea where radi_nume_radi in ($listaRadicados) and estado=1 and usua_codi_ori=$codUsario";
        $rsFechaTarea = $db->query($sqlFechaTarea);
        if($rsFechaTarea->fields["FECHA_MAXIMA"])
            $fechaMaximaTarea = $rsFechaTarea->fields["FECHA_MAXIMA"];
        return $fechaMaximaTarea;
    }
    function obtenerFechaTareaRadicado($db, $listaRadicados, $codUsario){
         $fechaMaximaTarea = date("Y-m-d");
        $sqlFechaTarea = "select fecha_maxima from tarea where radi_nume_radi in ($listaRadicados) and usua_codi_dest = $codUsario";        
        $rsFechaTarea = $db->query($sqlFechaTarea);
        if($rsFechaTarea->fields["FECHA_MAXIMA"])
            $fechaMaximaTarea = $rsFechaTarea->fields["FECHA_MAXIMA"];
        return $fechaMaximaTarea;
        
    }

    // Coloca en una matriz los datos del o los usuarios sacando los de la tabla usuarios_radicado o usuario dependiendo del estado del documento
    function ObtenerListaUsuariosDocumento ($db, $radi_nume, $tipo="R") {
        $datos = array();
        if (strlen(trim($radi_nume)) != 20) return;
        if (substr($radi_nume, -1) == 1) {
            $rs = $db->query("select radi_nume_temp from radicado where radi_nume_radi=$radi_nume");
            $radi_nume = $rs->fields["RADI_NUME_TEMP"];
        }
        $rs = $db->query("select esta_codi, radi_usua_rem, radi_usua_dest, radi_cca, radi_lista_dest from radicado where radi_nume_radi=$radi_nume");
        $estado = $rs->fields["ESTA_CODI"];
        if ($estado==1 or $estado==7) { // Si el documento está en elaboración
            $lista_nombre = "";
            if ($tipo=="R") $lista_usr = $rs->fields["RADI_USUA_REM"];
            elseif ($tipo=="D") {
                $lista_usr = $rs->fields["RADI_USUA_DEST"];
                foreach (explode("-", $rs->fields["RADI_LISTA_DEST"]) as $tmp) {
                    if ((0 + $tmp) > 0) {
                        $datosLista = ObtenerDatosLista($tmp,$db);
                        $lista_nombre .= $datosLista['nombre'] . "<br>";
                    }
                }
            }
            else $lista_usr = $rs->fields["RADI_CCA"];
            $lista_usr = trim(str_replace ("-", "", str_replace ("--", ",", $lista_usr)));
            if ($lista_usr=="") return;
            $rs = $db->query("select usua_nomb, usua_apellido, usua_abr_titulo, usua_titulo, coalesce(usua_cargo_cabecera,usua_cargo) as usua_cargo_cabecera
                                  , usua_cargo, inst_nombre, usua_ciudad, depe_codi, usua_codi from usuario where usua_codi in ($lista_usr)");
            $usr = explode(",", $lista_usr);
            $usr_pos = 0;
            while ($rs and !$rs->EOF) {
                //Cargamos los datos en una matriz
                for ($i=0; $i<count($usr); ++$i) { //Buscamos la posición del usuario para que salgan ordenados
                    if ($usr[$i] == $rs->fields["USUA_CODI"]) {
                        $usr_pos = $i;
                        $i = count($usr);
                    }
                }
                $datos[$usr_pos]["usua_codi"] = $rs->fields["USUA_CODI"];
                $datos[$usr_pos]["usua_nomb"] = $rs->fields["USUA_NOMB"];
                $datos[$usr_pos]["usua_apellido"] = $rs->fields["USUA_APELLIDO"];
                $datos[$usr_pos]["usua_abr_titulo"] = $rs->fields["USUA_ABR_TITULO"];
                $datos[$usr_pos]["usua_titulo"] = $rs->fields["USUA_TITULO"];
                $datos[$usr_pos]["usua_cargo"] = $rs->fields["USUA_CARGO"];
                $datos[$usr_pos]["usua_cargo_cabecera"] = $rs->fields["USUA_CARGO_CABECERA"];
                $datos[$usr_pos]["usua_institucion"] = $rs->fields["INST_NOMBRE"];
                $datos[$usr_pos]["usua_ciudad"] = $rs->fields["USUA_CIUDAD"];
                $datos[$usr_pos]["depe_codi"] = $rs->fields["DEPE_CODI"];
                $datos[$usr_pos]["lista_nombre"] = $lista_nombre;
                $datos[$usr_pos]["usua_firma_path"] = "";
                $rs->MoveNext();
            }
            return $datos;
        } else { //Si el documento ya está firmado
            if ($tipo=="R") $usr_tipo = 1;
            elseif ($tipo=="D") $usr_tipo = 2;
            else $usr_tipo = 3;
            $rs = $db->query("select usua_nombre, usua_apellido, usua_abr_titulo, usua_titulo, usua_cargo, usua_institucion,
                                  usua_ciudad, usua_area_codi, lista_nombre, usua_firma_path, usua_codi
                              from usuarios_radicado
                              where radi_nume_radi=$radi_nume and radi_usua_tipo=$usr_tipo
                              order by usua_radi_codi");
            $usr_pos = 0;
            while ($rs and !$rs->EOF) {
                //Cargamos los datos en una matriz
                $datos[$usr_pos]["usua_codi"] = $rs->fields["USUA_CODI"];
                $datos[$usr_pos]["usua_nomb"] = $rs->fields["USUA_NOMBRE"];
                $datos[$usr_pos]["usua_apellido"] = $rs->fields["USUA_APELLIDO"];
                $datos[$usr_pos]["usua_abr_titulo"] = $rs->fields["USUA_ABR_TITULO"];
                $datos[$usr_pos]["usua_titulo"] = $rs->fields["USUA_TITULO"];
                $datos[$usr_pos]["usua_cargo"] = $rs->fields["USUA_CARGO"];
                $datos[$usr_pos]["usua_cargo_cabecera"] = $rs->fields["USUA_CARGO"];
                $datos[$usr_pos]["usua_institucion"] = $rs->fields["USUA_INSTITUCION"];
                $datos[$usr_pos]["usua_ciudad"] = $rs->fields["USUA_CIUDAD"];
                $datos[$usr_pos]["depe_codi"] = $rs->fields["USUA_AREA_CODI"];
                $datos[$usr_pos]["lista_nombre"] = $rs->fields["LISTA_NOMBRE"];
                $datos[$usr_pos]["usua_firma_path"] = $rs->fields["USUA_FIRMA_PATH"];
                ++$usr_pos;
                $rs->MoveNext();
            }
            return $datos;
        }
    }
    //funcion para obtener si es usuario subrogante o subrogado
    
    //$mensaje = "perfil,estado" 
    function usrMensajeSubrogacion($db,$usr_codigo_verifica,$mensaje){
        $tipoMensaje="";
        $sql= "select usua_visible,usua_subrogado,usua_subrogante
               from usuarios_subrogacion where usua_visible=1 
               and (usua_subrogado = $usr_codigo_verifica or usua_subrogante = $usr_codigo_verifica)";        
         $rs= $db->conn->query($sql);
              if ($rs && !$rs->EOF){
                  $codigoSubrogante=$rs->fields['USUA_SUBROGANTE'];
                  $subrnte=array();
                  $subrnte=ObtenerDatosUsuario($codigoSubrogante,$db);
                  $usua_subrogante=$subrnte["usua_nombre"]." ".$subrnte["usua_apellido"];
                  $codigoSubrogado=$rs->fields['USUA_SUBROGADO'];
                  $subgado=array();
                  $subgado=ObtenerDatosUsuario($codigoSubrogado,$db);
                  $usua_subrogado=$subgado["usua_nombre"]." ".$subgado["usua_apellido"];                               
                      if ($usr_codigo_verifica==$codigoSubrogado){//retorna subrogado
                        if ($mensaje=='perfil')
                          $tipoMensaje=" (Subrogado)";
                        else
                          $tipoMensaje="Subrogado por $usua_subrogante";  
                      }
                      else{//retorna subrogante
                          if ($mensaje=='perfil')
                            $tipoMensaje=" (Subrogante)";
                           else
                            $tipoMensaje="Subrogante de $usua_subrogado";
                      }
              }
         //echo $tipoMensaje;
        return $tipoMensaje;
    }
    function utilSqlSubrogacion($depe_codi){
        $sql="select 
             --Subrogado
               usua_apellido || ' ' || usua_nomb || ' ' ||
              case when usua_codi in 
              (select usua_subrogado from usuarios_subrogacion where usua_visible=1) = true then
              '(Subrogado)' else '' end || ' ' ||
              --Subrogante
              case when usua_codi in 
              (select usua_subrogante from usuarios_subrogacion where usua_visible=1) = true then
              '(Subrogante)' else '' end as usua_nombre, usua_codi 
              from usuario where usua_esta=1 
              and usua_login not like 'UADM%'
              and visible_sub=1
              and depe_codi=".$depe_codi." order by 1";
        return $sql;
    }
//obtiene usuarios de bandeja compartida del jefe
function enviarCorreoUsrBandejaCompartida($usua_remi,$usua_codi,$radi_nume,$db,$ruta_raiz){
    include_once "$ruta_raiz/include/tx/Tx.php";
    $tx = new Tx($db);
    $sql = "select usua_codi from bandeja_compartida where usua_codi_jefe = $usua_codi";    
    $rs = $db->conn->query($sql);
      while ($rs and !$rs->EOF) {                 
                 $mail_param["usuario"] = $rs->fields["USUA_CODI"]; 
                 
                 $tx->enviarMail($usua_remi, $rs->fields["USUA_CODI"], $radi_nume,'Documento Recibido','2', $mail_param);
                 $rs->MoveNext();
             }
      
}
//envia correo a los que tienen bandeja compartida 
//si el documento es para el jefe
function correoBandejaCompartidas($radicados,$db,$ruta_raiz,$tipo){
    $radRadi = array();
    $usuRadi = array();
    foreach($radicados as $radi_nume)
    {
        $radRadi=ObtenerDatosRadicado($radi_nume, $db);
        $usua_remi =  $radRadi["usua_rem"];        
        $usua_remiInst = str_replace('-','',$usua_remi);
        $sqlIn = "select inst_codi from usuario where usua_codi = $usua_remiInst";
        
        $rsIn = $db->conn->query($sqlIn);
        if ($rsIn && !$rsIn->EOF){
            $inst_codi_remi=$rsIn->fields["INST_CODI"]; 
        }
        $destinatarios = $radRadi["usua_dest"];//obtengo destinatarios
        foreach (explode('-',$destinatarios) as $usua_codi){//inicio for
            if ($usua_codi!=''){
            $sql = "select cargo_tipo, inst_codi, depe_codi 
                    from usuario where usua_codi = $usua_codi";
            
            $rs = $db->conn->query($sql);
             if ($rs && !$rs->EOF){
                $cargo_usr = $rs->fields["CARGO_TIPO"];
                $inst_codi_dest = $rs->fields["INST_CODI"];
                if ($tipo==1 and ($inst_codi_dest==$inst_codi_remi)){//electronico
                    enviarCorreoUsrBandejaCompartida($usua_remiInst,$usua_codi,$radi_nume,$db,$ruta_raiz);                   
                }else{
                    if ($cargo_usr==1 and ($inst_codi_dest==$inst_codi_remi)){                    
                    //buscar bandeja compartida del jefe
                    enviarCorreoUsrBandejaCompartida($usua_remiInst,$usua_codi,$radi_nume,$db,$ruta_raiz);
                    }
                }
             }
            }//usua codi
        }//for
        
    }//foreach
}
//Obtener responsable de area
//depe_codi: dependencia
//retorna 1 = nombre, otra valor retorna si existe
function obtenerResponsableArea($depe_codi,$db,$retorna){
    $sql = "select * from usuarios where depe_codi=$depe_codi and usua_responsable_area=1";
    //echo $sql;
    $rs = $db->conn->query($sql);
    if(!$rs->EOF) {
        
        $usuarioRes = $rs->fields["USUA_CODI"];
        if ($usuarioRes!=0 || $usuarioRes!='')
            $usuarioRes=$usuarioRes;
        else
            $usuarioRes = 0;
        $usuaNombre = $rs->fields["USUA_NOMB"]." ".$rs->fields["USUA_APELLIDO"];
    }else
        $usuarioRes=0;
    
    if ($retorna==1)
       return $usuaNombre;
    else
      return $usuarioRes;
}
//eliminar de lista a usuario
function eliminarDlista($usr_codigo,$db){
    $sqlista = "select * from lista_usuarios where usua_codi = $usr_codigo";
    $rslista = $db->conn->query($sqlista);
    while ($rslista && !$rslista->EOF) {
       $deldeLista = "delete from lista_usuarios where usua_codi = $usr_codigo";
       $db->conn->Execute($deldeLista);
       $rslista->MoveNext();         
    }
}
function obtenerAreasAdmin($usr_codigo,$inst_codigo,$perfil,$db){
  if ($usr_codigo!=''){
    if ($perfil==1){//perfil administrador
        
        $sql ="select * from usuario_dependencia u    
            where usua_codi = $usr_codigo";
   
           $rsDepePadre=$db->conn->query($sql);
           $depeCodiTmp = $rsDepePadre->fields['DEPE_CODI_TMP'];
          
           $depeOrdenTmp = str_replace(',0,', ',', $depeCodiTmp);
           $depeOrdenTmp = substr($depeOrdenTmp,1);
                     
    return  $depeOrdenTmp;
    }else
        return '';
  }else
      return '';
}
function obtenerComboPermiso($usr_codigo,$inst_codigo,$db,$perfil){
     $sql="select * from usuario_dependencia where usua_codi = $usr_codigo 
        and inst_codi = $inst_codigo";    
        //echo $sql;
        $rs=$db->conn->query($sql);
}
//Obtener datos envio fisico
function ObtenerDatosEnvioFisico($hist_codi,$db)
    {//Obtiene datos del radicado en función del texto
       //if (trim($hist_codi)=="") return array();
        $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $isqlTEXT = "select * from hist_envio_fisico
                     where hist_codi=".trim($hist_codi);        
        $vector = array();
        $rs=$db->query($isqlTEXT);
        $vector["hist_fech_envio"] = $rs->fields["HIST_FECH_ENVIO"];
        $vector["radi_nume_radi"] = $rs->fields["RADI_NUME_RADI"];
        $vector["usua_codi_enviado"] = $rs->fields["USUA_CODI_ENVIADO"];
        $vector["responsable"] = $rs->fields["USUA_RESPONSABLE"];
        $vector["estado"] = $rs->fields["ESTADO"];
        return $vector;
    
}
//verificar si existe usuario de la misma entidad en copia
//en documento
//usuario_copia, institucion
function verificarInstitucion($para,$inst_codi,$db){
    $combo_mostrar = 0;
    if ($para!=''){        
                foreach (explode('-',$para) as $usr_codigo){                   
                    if ($usr_codigo!=''){
                        $usrIns=ObtenerDatosUsuario($usr_codigo, $db);                        
                        if ($inst_codi==$usrIns['inst_codi'])                           
                             $combo_mostrar = 1;
                    }
                }
    }
                return $combo_mostrar;
}
//obtiene usuarios para o copia de un radicado
//$radicadosSel array de radicado
//tipo destinatario o copia, todos = para y copia
function ObtenerUsuariosRadicado($db,$radicadosSel,$tipo='para'){
            $usrDocDestino = array();
            $datosRadis = array();
            $usCodDestino="";
            $datosPadre=array();
            foreach ($radicadosSel as $radi_nume) {//for
                //obtener padre
              if ($radi_nume!=''){
                $datosPadre=ObtenerDatosRadicado($radi_nume, $db);
                $radi_nume_padre = $datosPadre['radi_nume_temp'];
                if ($radi_nume_padre!=''){//if radi seleccionado
                    if ($tipo=='todos'){//para y copia
                      $datosRadis=ObtenerDatosRadicado($radi_nume_padre, $db);
                      $usr_cod_dest=$datosRadis['usua_dest'].$datosRadis['cca'];
                    }
                    elseif ($tipo=='para'){
                        $datosRadis=ObtenerDatosRadicado($radi_nume, $db);
                        $usr_cod_dest=$datosRadis['usua_dest'];
                    }
                    elseif($tipo=='cca'){
                        $datosRadis=ObtenerDatosRadicado($radi_nume, $db);
                        $usr_cod_dest=$datosRadis['cca'];
                    }
                    //nombres de usuarios
                    foreach (explode("-", $usr_cod_dest) as $usua_tmp) {//destinatarios
                        if ($usua_tmp!=''){
                            $sql="select usua_nombre from usuario
                                where usua_codi = $usua_tmp";                            
                             $rs = $db->conn->Execute($sql);
                            while (!$rs->EOF) { //
                                $usNombreUsuarios .=$rs->fields["USUA_NOMBRE"]."<br>";
                                $rs->MoveNext();
                            }//while
                        }
                    }//destinatarios
                }//if radi seleccionado
              }
            }//fin for
            return $usNombreUsuarios;
}
//obtener areas segun nivel de dependencia
//depe_codi dependencia padre
//nivel, cuantos niveles deseo bajar
//retorna solo codigos para incluir en un sql
function areasHijasNivelDependencia($db, $depe_codi,$nivel=2){
    
    //seleccionamos las hijas de la dependencia que se envia
    $depeCodiH = "";
    $sql = "select depe_codi from dependencia where depe_codi_padre = $depe_codi";
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    while (!$rs->EOF) { //dependencias hijas
        $depeCodiH.=",".$rs->fields["DEPE_CODI"];
        
            $depeCodiH.=areasHijasNivelHijas($db,$rs->fields["DEPE_CODI"],$nivel-1);
        
        $rs->MoveNext();
    }
    return $depeCodiH;
}
//funcion anidad a areasHijasNivelDependencia, busca las hijas de la hija
function areasHijasNivelHijas($db,$depe_codi,$nivel){
    
   
    $sql = "select depe_codi from dependencia where depe_codi_padre = $depe_codi";
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    while (!$rs->EOF) { //dependencias hijas
        
            //busco las hijas hasta el nivel
            $depeCodiH.=",".$rs->fields["DEPE_CODI"];
            //if ($nivel>0)
            //for($i=0;$i<$nivel-1;$i++){                 
                $depeCodiH.=areasHijasNivelHijas($db,$rs->fields["DEPE_CODI"],$nivel-1);
                
            //}
            $rs->MoveNext();
    }
    return $depeCodiH;
}
?>