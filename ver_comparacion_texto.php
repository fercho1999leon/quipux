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
*	Autor               Iniciales		Fecha (dd/mm/aaaa)
*	Mauricio Haro		MH              06-08-2009
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
**/

    function &fn_validar_html($html) {
        // Filtramos y depuramos el codigo html eliminando tags especiales, estilos, imagenes, etc.
        $html = preg_replace(':<input (.*?)type=["\']?(hidden|submit|button|image|reset|file)["\']?.*?>:i', '', $html);
        $html = preg_replace(':<style.*?>.*?</style>:is', '', $html);
        $html = preg_replace(':<title.*?>.*?</title>:is', '', $html);
        $html = preg_replace(':<img.*?/>:is', '', $html);
        $html = preg_replace(':<meta.*?>:is', '', $html);
        $html = preg_replace(':<!--.*?-->:is', '', $html);
        $html = preg_replace(':<br.*?>:is', '<br>', $html);
        $html = preg_replace(':<li.*?>:is', '<li><br>', $html);
        $html = preg_replace(':<td.*?>:is', '<td>', $html);
        $html = preg_replace(':<th.*?>:is', '<th>', $html);
        $html = preg_replace(':<p .*?>:is', '', $html);
        $html = str_replace("</meta>", "", $html);
        $html = str_replace("</p>", "<br>", $html);
        $origen = array("á","é","í","ó","ú","ñ","Á","É","Í","Ó","Ú","Ñ");
        $destino = array("&aacute;","&eacute;","&iacute;","&oacute;","&uacute;","&ntilde;","&Aacute;","&Eacute;","&Iacute;","&Oacute;","&Uacute;","&Ntilde;");
        $html = str_replace($origen, $destino, $html);
        //$html = nl2br($html);
        $html = str_replace(chr(10), "<br>", $html);
        $html = str_replace(chr(13), "<br>", $html);
        $html = str_replace("<br><br>", "<br>", $html);
        $html = str_replace("<br><br>", "<br>", $html);
        return $html;
    }

    function fn_limpiar_html($html) {
        $html = preg_replace(':<.*?>:is', '', $html);
        $html = str_replace("&nbsp;", " ", $html);
        return trim($html);
    }

    function marcar_diferencias($txt, $ok) {
        // Instrucciones en las que se debe poner el fondo dentro de los tags de inicio y fin para que los estilos sean tomados en cuenta
        // Se debe tambien realizar un filtro previo en fv_validar_html()
        $tag_abre = array("<li>","<th>","<td>");
        $tag_cierra = array("</li>","</th>","</td>");
        $html = "";
        $num = count ($txt);
        for ($i=0 ; $i<$num ; ++$i) {
            if (fn_limpiar_html($txt[$i])=="") {
                // En el caso de tags como <ol>, <table>, etc. no se aumenta nada
                $html .= $txt[$i];
            } else {
                $flag = true;
                $tmp = $txt[$i];
                // En el caso de tags como <td>, <li>, etc el fondo se lo pone entre los tags de inicio y fin,
                // caso contrario el fondo contiene a todo el texto
                foreach ($tag_abre as $tag) {
                    if (strpos($txt[$i],$tag) !== false) {
                        $tmp = str_replace($tag, $tag.'<span class="fondo' . $ok[$i] . '">', $tmp);
                        $flag = false;
                    }
                }
                foreach ($tag_cierra as $tag) {
                    if (strpos($txt[$i],$tag) !== false) {
                        $tmp = str_replace($tag, "</span>$tag", $tmp);
                        $flag = false;
                    }
                }
                if ($flag)
                    $html .= '<span class="fondo' . $ok[$i] . '">' . $txt[$i] . '</span><br>';
                else
                    $html .= trim($tmp);
            }
        }
        return $html;
    }

    session_start();
    $ruta_raiz = ".";
    include_once "$ruta_raiz/rec_session.php";


    $txt_anterior = "";
    $txt_actual   = "";


    $sql = "select text_texto from radi_texto where text_codi=";
    $rs = $db->conn->Execute($sql.$_POST["texto_anterior"]);
    if (!$rs->EOF)
        $txt_anterior = $rs->fields["TEXT_TEXTO"];
    $rs = $db->conn->Execute($sql.$_POST["texto_actual"]);
    if (!$rs->EOF)
        $txt_actual = $rs->fields["TEXT_TEXTO"];

    $txt_anterior = fn_validar_html($txt_anterior);
    $txt_actual   = fn_validar_html($txt_actual);
    
//    comparar($txt_anterior, $txt_actual);

        $txt1 = explode("<br>",$txt_anterior);
        $txt2 = explode("<br>",$txt_actual);
        $num1 = count($txt1);
        $num2 = count($txt2);
        $pos1 = 0;
        $pos2 = 0;
        $html1 = "";
        $html2 = "";
        $ok1 = array();
        $ok2 = array();

        for ($i=0 ; $i<$num1 ; ++$i) {
            $ok1[$i] = 0;
        }
        for ($i=0 ; $i<$num2 ; ++$i) {
            $ok2[$i] = 0;
        }

        // Verificamos y marcamos los parrafos coincidan
        // Se evalua tambien el orden de los parrafos segun como aparecen en el texto 1
        while ($pos1 < $num1) {
            if (fn_limpiar_html($txt1[$pos1]) != "") {
                for ($i=$pos2 ; $i<$num2 ; ++$i) {
                    if (fn_limpiar_html($txt1[$pos1]) == fn_limpiar_html($txt2[$i])) {
                        //Si coinciden los parrafos marcamos 1 en los arreglos
                        $ok1[$pos1] = 1;
                        $ok2[$i] = 1;
                        $pos2 = $i;
                        $i = $num2;
                    }
                }
            }
            ++ $pos1;
        }

        // Verificamos y marcamos los parrafos coincidan y que estan en desorden
        $pos2 = 0;
        while ($pos2 < $num2) {
            if (fn_limpiar_html($txt2[$pos2]) != "") {
                for ($i=0 ; $i<$num1 ; ++$i) {
                    if (fn_limpiar_html($txt2[$pos2]) == fn_limpiar_html($txt1[$i])) {
                        //Si coinciden los parrafos marcamos 2 en los arreglos
                        if ($ok2[$pos2] == 0) {
                            $ok2[$pos2] = 2;
                        }
                        if ($ok1[$i] == 0) {
                            $ok1[$i] = 2;
                        }
                    }
                }
            }
            ++ $pos2;
        }

        //Ponemos background en los parrafos
        $html1 = marcar_diferencias($txt1, $ok1);
        $html2 = marcar_diferencias($txt2, $ok2);



/*
    //Muestra los caracteres de la cadena con sus valores ascci para eliminarlos si se requiere
    $cad = str_split($html2);
    foreach ($cad as $letra)
        echo "$letra - " . ord($letra)."<br>";
 /* */

?>


<html>
    <head>
        <title>Diferencia Entre Versiones de Documentos</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="estilos/orfeo.css">
        <style>
            .fondo0 { background-color: #CCFFCC; /* Texto no encontrado */}
            .fondo1 { background-color: white; }
            .fondo2 { background-color: #FFFFCC; /* Texto en diferente orden*/}
        </style>

    </head>
    <body>
        <center>
            <table border=2 cellspace=2 cellpad=2 WIDTH=95%  class="t_bordeGris" align="center">
                <tr><td colspan="2" align="center" class="titulos2">Diferencia Entre Versiones de Documentos</td></tr>
                <tr>
                    <td align="center" class="titulos2" width="50%">Versi&oacute;n Anterior</td>
                    <td align="center" class="titulos2" width="50%">Versi&oacute;n Actual</td>
                </tr>
                <tr>
                    <td valign="top" width="50%"><?=$html1?></td>
                    <td valign="top" width="50%"><?=$html2?></td>
                </tr>
            </table>

            <br>
            <table border="2" cellspace="2" cellpad="2" WIDTH="300"  class="t_bordeGris" align="center">
                <tr>
                    <td width="30" align="center"><div class="fondo0" style="height: 15px;width: 15px;border: thin solid #999999;">&nbsp;</div></td>
                    <td><font size="2">L&iacute;neas o p&aacute;rrafos diferentes entre los dos textos.</font></td>
                </tr>
                <tr>
                    <td width="30" align="center"><div class="fondo2" style="height: 15px;width: 15px;border: thin solid #999999;">&nbsp;</div></td>
                    <td><font size="2">L&iacute;neas o p&aacute;rrafos en un orden diferente.</font></td>
                </tr>
            </table>

            <br><br>
            <input type="button" name="btn_cancelar" value="Cerrar" class="botones_largo" onclick="fjs_popup_cerrar();">
<? /*
            <br><br><br>
            <textarea name="txt1" id="txt1" cols="150" rows="5"><?=$txt_anterior?></textarea>
                <br>
            <textarea name="txt2" id="txt2" cols="150" rows="5"><?=$txt_actual?></textarea>
            <br>
            <textarea name="txt1" id="txt1" cols="150" rows="5"><?=$html1?></textarea>
                <br>
            <textarea name="txt2" id="txt2" cols="150" rows="5"><?=$html2?></textarea>
 /*  */
?>
        </center>
    </body>
</html>
