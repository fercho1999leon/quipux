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

$ruta_raiz = "..";
if (!$db->driver){ $db = $this->db; }	//Esto sirve para cuando se llama este archivo dentro de clases donde no se conoce $db.
//validacion de asociados en documentos externos

$vradi_nume = substr($radi_nume,19,1);//radicado
$vradi_nume_tmp = substr($radi_nume_tmp,19,1);//radicado padre
$editar=1;
if (($vradi_nume==0 and $radi_nume_deri!='')) //|| ($vradi_nume==1 and $vradi_nume_tmp==2))
    $editar=0;
if (!isset ($orderNo)) $orderNo = 2;
    
switch($db->driver)
{
    case 'postgres':
    	$sqlFecha = "substr(radi_fech_ofic::text,1,19)";
        
        $usuarioSel = 0+$_SESSION["usua_codi"];
        $from_usr_recorrido = " radi_nume_radi in (select distinct radi_nume_radi from hist_eventos ".
                              " where usua_codi_ori=$usuarioSel or usua_codi_dest=$usuarioSel) ";
        
        $isql = "select -- Asociacion de documentos
                radi_nume_text as \"No. Documento\"
                ,radi_cuentai as \"No. Referencia\"
                ,$sqlFecha as \"DAT_Fecha Documento\"
                ,radi_nume_radi as \"HID_RADI_NUME_RADI\"
                ,radi_asunto  as \"Asunto\"
                ,ver_usuarios(radi_usua_actu::text,',') AS \"Usuario Actual\"
                ,ver_usuarios(radi_usua_rem,',<br>') AS \"Remitente\"
                ,ver_usuarios(radi_usua_dest,',<br>') AS \"Destinatario\"
                ,trad_descr as \"Tipo de Documento\"";
        if ($editar==1)
            $isql.=",'Antecedente' AS \"SCR_Acción\",'seleccionar_documento(\"'|| radi_nume_radi ||'\",\"A\");' as \"HID_FUNCIONA\"";

            $isql.=",case when radi_nume_asoc is null then 'Consecuente' else '' end AS \"SCR_Acción.\"
                ,'seleccionar_documento(\"'|| radi_nume_radi ||'\",\"C\");' as \"HID_FUNCIONC\"
            from (
                select r.radi_nume_text, radi_cuentai, r.radi_fech_ofic, r.radi_nume_radi ,r.radi_asunto ,r.radi_usua_actu
                , r.radi_usua_rem, r.radi_usua_dest, t.trad_descr, r.radi_nume_asoc
                from (select * from radicado b where 
                    radi_nume_text||' '||coalesce(upper(radi_cuentai),'') like '%".strtoupper($txt_documento)."%'
                    and radi_inst_actu = " . $_SESSION["inst_codi"] . "
                    and esta_codi in (0,1,2,3,4,5,6) and radi_nume_radi<>$radi_nume) as r
                left outer join tiporad t on r.radi_tipo=t.trad_codigo
                order by ".($orderNo+1)." $orderTipo *LIMIT**OFFSET*
            ) as a order by ".($orderNo+1)." $orderTipo";

//echo $isql."<hr>";

	break;
}
?>
