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

    session_start();
    $ruta_raiz = "..";
    include_once "$ruta_raiz/rec_session.php";
    if (isset ($replicacion) && $replicacion && $config_db_replica_tx_formenvio_ajax!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_tx_formenvio_ajax);
    $area = limpiar_sql($_GET["area"]);
    if (trim($area,",0123456789 ")!="") $area = 0 + $area;

    $where = "";
    if (($_GET["codTx"]==9)  and $_SESSION["depe_codi"] != $area)
        $where = " and (cargo_tipo=1 or usua_codi in (select usua_codi from permiso_usuario where id_permiso=29)) ";

    $sql = "select (usua_apellido || ' ' || usua_nomb)
                || ' ' || case when usua_codi in (select usua_subrogado from usuarios_subrogacion where usua_visible=1) = true then '(Subrogado)' else '' end
                || ' ' || case when usua_codi in (select usua_subrogante from usuarios_subrogacion where usua_visible=1) = true then '(Subrogante)' else '' end as usua_nombre
                , usua_codi
            from usuarios
            where usua_codi>0 and usua_esta=1 and visible_sub=1 and usua_login not like 'UADM%'
                and depe_codi in ($area) $where
            order by 1";
    //echo $sql;
    $rs_usr = $db->conn->Execute($sql);

    if ($_GET["codTx"]==8  ){
        $menu_usr  = $rs_usr->GetMenu2("usCodSelect[]", 0, false, true, 8," id='usCodSelect' class='select'" );
       
    }
    if ($_GET["codTx"]==9 or $_GET["codTx"]==69)//
        $menu_usr  = $rs_usr->GetMenu2("usCodSelect", 0, "0:&lt;&lt; Seleccione Usuario &gt;&gt;", false,""," id='usCodSelect' class='select'" );
         
    echo $menu_usr

    ?>
