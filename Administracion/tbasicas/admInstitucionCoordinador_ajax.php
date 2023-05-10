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

$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
    include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";

$error = "";

if ($slc_institucion != 0 and $slc_institucion != '') {
    $sql = "select * from institucion where inst_codi=".$slc_institucion;
    $rs = $db->conn->query($sql);

    $txtRuc = $rs->fields['INST_RUC'];
    $txtNombre = $rs->fields['INST_NOMBRE'];
    $txtSigla = $rs->fields['INST_SIGLA'];
    $txtLogo = $rs->fields['INST_LOGO'];

?>

<table width="80%" class="borde_tab">
<tr>
    <td align="center" class="titulos4" colspan="4"><font size="2">Ministerio Coordinador</font></td>
</tr>
</table>
<table width="80%" class="borde_tab">
    <tr>
        <td width="40%" align="center" class="titulos2">Ministerios Coordinadores</td>
        <td width="12%" align="center" class="listado2" rowspan="2">
            <input type="button" class="botones" value="Aceptar" onclick="institucionCoordinadora('','<?=$slc_institucion?>',1);">
        </td>
        <td width="48%" align="center" class="titulos2">Ministerio(s) Coordinador(es) de la Instituci&oacute;n <br><?=$txtNombre?></td>
    </tr>
    <tr>
        <td align="center" class="listado2">
        <?php
            $sql = "select
                        inst_nombre,
                        inst_codi
                    from
                        institucion
                    where
                        inst_estado=1
                        and inst_coordinador = 1
                    order by 1 asc";
                    /*
                    "select
                        inst_nombre,
                        inst_codi
                    from
                        institucion
                    where
                        inst_estado=1
                        and inst_coordinador = 1
                        and inst_codi not in (select inst_codi_coor from institucion_coordinador where inst_codi = ".$slc_institucion.")
                    order by 1 asc"
                    */
           
            $rs = $db->conn->Execute($sql);
             //var_dump($rs);
            echo $rs->GetMenu2('institucionSel[]', 0, false, true, 8, " id='institucion' class='select' style='width:400px;' ");
        ?>
        </td>
        <td valign="top" class="listado2">
            <table width="100%" class="borde_tab">
                <tr>
                    <td width="85%" align="center" class="titulos2">Instituci&oacute;n</td>
                    <td width="15%" align="center" class="titulos2">Acci&oacute;n</td>
                </tr>
                <?php
                $sql = 'select * from institucion_coordinador where inst_codi = '.$slc_institucion;
                $rs = $db->conn->query($sql);
                
                while (!$rs->EOF) {
                    $sqlInstCoor = 'select * from institucion where inst_codi = '.$rs->fields["INST_CODI_COOR"];
                    $rsInstCoor = $db->conn->query($sqlInstCoor);
                    //$datosInst = ObtenerDatosUsuario($rs->fields["USUA_CODI"], $db);S
                    echo '<tr>';
                        echo '<td align="left" class="listado2_ver">'.$rsInstCoor->fields["INST_NOMBRE"].'</td>';
                        echo '<td align="left" class="listado2"><input type="button" value="eliminar" onclick="institucionCoordinadora('.$rs->fields["INST_COOR_CODI"].','.$slc_institucion.',2);" class="botones"></td>';
                    echo '</tr>';
                    $rs->MoveNext();
                }
                ?>
            </table>
        </td>
    </tr>
</table>
<?php } ?>