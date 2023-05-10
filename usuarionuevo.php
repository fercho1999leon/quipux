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

    $ruta_raiz = ".";
    include_once "$ruta_raiz/funciones_interfaz.php";

?>
<html>
    <?echo html_head(true,true); /*Imprime el head definido para el sistema*/?>

    <script language="JavaScript" type="text/JavaScript">
        function popup_main()
        {
            var x = (screen.width - 1400) / 2;
            var y = (screen.height - 750) / 2;
            url = "./Administracion/usuarios/cambiar_password.php?krd=<?=$_GET['krd']?>&code=<?=$_GET['code']?>";
            ventana=window.open(url,"QUIPUX","toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes, width=1400, height=750");
            ventana.moveTo(x, y);
            ventana.focus();
        }
    </script>

    <body class="f-default light_slate" onLoad='focus();'>
        <div id="wrapper">
        <? echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
        <? echo html_validar_browser(); /*Valida el browser*/ ?>
        <div id="mainbody"><div class="shad-1"><div class="shad-2"><div class="shad-3"><div class="shad-4"><div class="shad-5">
        <br /><br /><br />
        <table align="center" width="100%" cellpadding="0" cellspacing="0" class="mainbody">
            <tr valign="top" align="center">
                <td class="left"  align="center" width="100%">
                    <h1>Sistema de Gesti&oacute;n Documental - QUIPUX</h1><br /><br />
                    Para Ingresar al sistema, haga click &nbsp
                    <a href="javascript:popup_main();"><font color="blue" face="Verdana" size="3" ><b>&quot;AQU&Iacute;&quot;</b></font></a>
                </td>
            </tr>
        </table>
        <br /><br /><br />

        </div></div></div></div></div></div>
        <? echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
        </div>
    </body>
</html>
