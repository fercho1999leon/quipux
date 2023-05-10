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
    $ruta_raiz = "../..";
    require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
    p_register_globals(array());
    include_once "$ruta_raiz/rec_session.php";
    include_once "$ruta_raiz/obtenerdatos.php";

    $area = ObtenerDatosDependencia($_GET['depeCodi'],$db);

    if(trim($area['padre'])!='')
    {
        $sqlNomPadre = 'select depe_nomb from dependencia where depe_estado=1 and depe_codi = '.$area['padre'];
        $rsPadre = $db->query($sqlNomPadre);
    }

    if(trim($area['archivo'])!='')
    {
        $sqlNomArch = 'select depe_nomb from dependencia where depe_estado=1 and depe_codi = '.$area['archivo'];
        $rsArch = $db->query($sqlNomArch);
    }

    if(trim($area['plantilla'])!='')
    {
        $sqlNomPlan = 'select depe_nomb from dependencia where depe_estado=1 and depe_codi = '.$area['plantilla'];
        $rsPlan = $db->query($sqlNomPlan);
    }

    //Obtener datos del Jefe de Área
    $datosJefe = ObtenerJefeArea($_SESSION["inst_codi"], $_GET['depeCodi'], '1', $db);
    $tituloJefe = 'Datos del Jefe de &Aacute;rea';
    if($datosJefe['ciudad'])
    {
        $codigoCiu = $datosJefe['ciudad'];
        $ciudad = ObtenerCiudadUsua(' ciudad ', ' id = '. $codigoCiu, $db);
    }

    if($_GET['verLis'] == '2'){
        $pagina = 'value="Cerrar" onClick="window.close();"';
    }else
        $pagina = 'value="Regresar" onClick="location=\'../dependencias/mnu_dependencias.php\'"';
    //var_dump($area);
    $datos = '
            <table class="borde_tab" width="100%">
                <tr><td align="center" class="titulos4" colspan="2"><font size="2">Información del &Aacute;rea Seleccionada</font></td></tr>
                <tr>
                    <td width="40%" align="left" class="titulos2">&Aacute;rea Padre:</td>
                    <td class="listado2_ver">'. $rsPadre->fields['DEPE_NOMB'] .'</td>
                </tr>
                <tr>
                    <td align="left" class="titulos2">Nombre:</td>
                    <td class="listado2_ver">'. $area['nombre'] .'</td>
                </tr>
                <tr>
                    <td align="left" class="titulos2">Sigla:</td>
                    <td class="listado2_ver">'. $area['sigla'] .'</td>
                </tr>
                <tr>
                    <td align="left" class="titulos2">Ciudad:</td>
                    <td class="listado2_ver">'. $area['ciudad'] .'</td>
                </tr>
                <tr>
                    <td align="left" class="titulos2">Ubicaci&oacute;n del Archivo F&iacute;sico:</td>
                    <td class="listado2_ver">'. $rsArch->fields['DEPE_NOMB'] .'</td>
                </tr>
                <tr>
                    <td align="left" class="titulos2">Área de la que se copiar&aacute; la plantilla del documento:</td>
                    <td class="listado2_ver">'. $rsPlan->fields['DEPE_NOMB'] .'</td>
                </tr>
                <tr>
                    <td align="center" class="listado2_ver" colspan="2">
                    
                    </td>
                </tr>
            </table>

            <br>
            <table width="100%" class="borde_tab">
            <tr>
                <td align="center" class="titulos4" colspan="4"><font size="2">'.$tituloJefe.'</font></td>
            </tr>';

            if(trim($datosJefe['usua_codi'])!='') {
            $datos .= '<tr>
                <td width="15%" align="left" class="titulos2">Puesto:</td>
                <td class="listado2_ver">'.$datosJefe['cargo'].'</td>
                <td width="15%" align="left" class="titulos2">T&iacute;tulo:</td>
                <td class="listado2_ver">'.$datosJefe['titulo'].'</td>
            </tr>
            <tr>
                <td align="left" class="titulos2">Nombre:</td>
                <td class="listado2_ver">'.$datosJefe['nombre'].'</td>
                <td align="left" class="titulos2">E-mail:</td>
                <td class="listado2_ver">'.$datosJefe['email'].'</td>
            </tr>';
            if(isset ($ciudad['nombre'])) {
            $datos .= '<tr>
                        <td align="left" class="titulos2">Ciudad:</td>
                        <td class="listado2_ver" colspan="3">'.$ciudad['nombre'].'</td>
                       </tr>';
                }
            } else {
            $datos .= '<tr>
                        <td align="center" class="listado2_ver" colspan="4"><font size="2">El &Aacute;rea aun no tiene asignado un Jefe</font></td>
                       </tr>';
            }
            $datos .= '</table>';

    echo $datos;
?>
