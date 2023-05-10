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
$ruta_raiz = "..";
include_once "$ruta_raiz/funciones.php";
//p_register_globals(array());

?>
<table border=0 width="100%"  cellpad=2 cellspacing='0' valign='top' align='center' >
    <tr><td width='100%' >
    <table align="center" cellspacing="0" cellpadding="0" width="100%" class="borde_tab">
    <tr ><td >
    <span class="listdo1">
    <form name=form_busq_rad action='<?=$pagina_actual?>?estado_sal=<?=$estado_sal?>&tpAnulacion=<?=$tpAnulacion?>&estado_sal_max=<?=$estado_sal_max?>&pagina_sig=<?=$pagina_sig?>&dep_sel=<?=$dep_sel?>&nomcarpeta=<?=$nomcarpeta?>' method=post>
    Buscar <?=$_SESSION["descRadicado"]?>(s) (Separados por coma)
    <input name="busqRadicados" type="text" size="60" class="tex_area" value="<?=$busqRadicados?>">
    <input type=submit value='Buscar ' name=Buscar valign='middle' class='botones'>

<?

        if ($busqRadicados) {
            $busqRadicados = trim(limpiar_sql($busqRadicados));
            $textElements = split (",", $busqRadicados);
            $newText = "";
            $i = 0;
            foreach ($textElements as $item) {
                $item = trim ( strtoupper($item) );
                if ($item) {
                if ($i != 0) $busq_and = "||"; else $busq_and = " ";
                    $busq_radicados_tmp .= " $busq_and coalesce($varBuscada,'')";
                    $i++;
                }
                $busq_radicados_tmp .= " ilike '%$item%' ";
            } //FIN foreach

        $dependencia_busq2 .= " and ($busq_radicados_tmp) ";
        } //FIN if ($busqRadicados)*/

?>
aaaaaaaaaaa
	</form>
	 </span>
	</td></tr>
	</table>
	<td/>
  <tr/>
</table>
