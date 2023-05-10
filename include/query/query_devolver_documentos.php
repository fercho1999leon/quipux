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

// Codigo modificado por M. Haro - email: mauricioharo21@gmail.com
// se incluyo un select adicional y *LIMIT**OFFSET* en varios queries para mejorar el rendimiento de la BDD
// La función ver_usuarios terda mucho tiempo en ejecutarse y cuando son muchos registros la ejecución de
// esta consulta se hace muy pesada.
// Para mejorar esto se cambiaron algunas librerias de ADODB para que al momento de realizar el count elimine la función
// y el limit y el offset se los pone en el query interior para que la función se ejecute solo para los registros que se van a mostrar.
// Adicionalmente se elimino la ejecución de la función en el count del paginador
// Archivos ADODB: (revisión svn 456)
// - adodb/adodb-lib.inc.php    - function _adodb_getcount()
// - adodb/drivers/adodb-postgres7.inc.php  - function SelectLimit()


switch($db->driver) {
    case 'postgres':
        //$whereFiltro = " and upper(radi_nume_text) like upper('%$txt_documento%')";
        $whereFiltro = " and (UPPER(radi_nume_text) like '%".trim(strtoupper($busqRadicados))."%'
                              or UPPER(radi_asunto) like '%".trim(strtoupper($busqRadicados))."%') ";

            if ($orderNo=='') $orderNo=4;
            $isql = "select -- Devolucion documentos
                    radi_nume_radi as \"CHK_CHKANULAR\"
                    ,ver_usuarios(radi_usua_rem,',') as \"De\"
                    ,ver_usuarios(radi_usua_dest,',') as \"Para\"
                    ,radi_asunto as \"Asunto\"
                    ,substr(radi_fech_radi::text,1,19) || '$descZonaHoraria' as \"DAT_Fecha Documento\"
                    ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                    ,radi_nume_text as \"Número Documento\"
                    ,trad_descr as \"Tipo Documento\"
                    from (
                        select b.radi_nume_radi, b.radi_usua_rem, b.radi_usua_dest, b.radi_asunto
                        , b.radi_fech_radi, 1, b.radi_nume_text, b.radi_cuentai, td.trad_descr
                        from (select * from radicado where esta_codi=6 and radi_nume_radi::text like '%1'
                        and radi_inst_actu = " . $_SESSION["inst_codi"] . " $whereFiltro ) as b
                        left outer join tiporad td on b.radi_tipo=td.trad_codigo
                        order by ".($orderNo+1)." $orderTipo *LIMIT**OFFSET*
                    ) as a order by ".($orderNo+1)." $orderTipo";
        break;
    }
//echo $isql;
?>
