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

/******************************************************************************************
**  Clase que maneja tablas recursivas y muestra los listados por niveles.               **
**                                                                                       **
**  Ejemplos de uso al final del archivo.                                                **
******************************************************************************************/

class FuncionesRecursivas {

    ////////  REQUISITOS  ///////
    var $db;  // Conexion con la BDD; parámetro enviado al momento de crear el objeto FuncionesRecursivas

    var $tabla; // Nombre de la tabla recursiva en la BDD
    var $id_tabla; // Campo de la tabla (generalmente el PK) que contiene el código del registro a obtener
    var $id_padre; // Campo de la tabla que contiene el código del "campo padre" que referencia a la misma tabla (FK)
    var $padre; // variable que contiene el id del padre (ver funcion buscar_padre() )
    var $campo; // Array - Contiene la lista de campos que se mostrarán en la tabla (ver funcion add_campo() )
    var $display; // Array - Contiene algunos estilos con los que se muestra la tabla
    var $resaltado; // Array - Contiene la lista de registros que deberan ser resaltados (en base al codigo) (ver funcion resaltar_fila() )
    var $tabulacion; // Numero de espacios en blanco con que se tabulan los niveles

    var $sql;  // Variable en la que se guarda el query de la función recursiva (ver funcion set_sql() )
    var $condicion; // Condiciones extras para el query de la función recursiva (ver funcion set_sql() )

    ////////  VARIABLES DE LA FUNCION  ///////
    var $respuesta; // Variable en la que se guarda la respuesta
    var $variables_jsc; // Variable para controlar el contraer y expander la tabla


/******************************************************************************************
**  Inicializa la Clase.                                                                 **
**  Requiere:                                                                            **
**    - $db      Conección con la BDD                                                    **
******************************************************************************************/
    function FuncionesRecursivas($db) {
        $this->db = $db;
        $this->sql = "";
        unset ($this->campo);
        unset ($this->resaltado);
        $this->condicion = "";
        $this->tabulacion = 8;
        $this->expander_tabla = true;
        $this->variables_jsc = "";
        unset ($this->display);
        $this->display["id"] = ""; //Contiene un ID por si se muestran varias tablas recursivas
        $this->display["link"] = "style='color: blue'";
        $this->display["tabla"] = "width='100%' border='1'";
        $this->display["resaltar"] = "class='titulos2'";
        $this->display["expander"] = true; // Expande o comprime la tabla
        $this->display["debug"] = false; // Imprime los queries ejecutados
        $this->display["order"] = '1 asc'; // Orden en la que se presentan los datos
    }


/***************************************************************************************************************
**  Añade los campos que se mostraran en la tabla.                                                            **
**  Requiere:                                                                                                 **
**    - $nombre    Nombre que se mostrará en la cabecera de la columna                                        **
**    - $tamano    Tamaño en porcentaje con el que se mostrará la columna                                     **
**    - $campo     Nombre del campo en la BDD (Se puede utilizar funciones, concatenar campos, cadenas, etc.) **
**    - $tipo      Tipo de campo en caso de que sea un link "L" a una función javascript                      **
**    - $funcion   Nombre de la funcion javascript                                                            **
**    - $campos_funcion   Parametros de la funcion Javascript (campos de la BDD)                              **
**                                                                                                            **
**  Retorna:                                                                                                  **
**    Almacena los campos en el arreglo $this->campo                                                          **
**  Ejemplos de uso al final del archivo.                                                                     **
***************************************************************************************************************/
    function add_campo($nombre, $tamano, $campo, $tipo="", $funcion="", $campos_funcion="") {
        $num_campo = count($this->campo);
        $this->campo[$num_campo]["campo"] = $campo;
        $this->campo[$num_campo]["alias"] = "c$num_campo"; // Se renombran los campos para evitar conflictos
        $this->campo[$num_campo]["nombre"] = $nombre;
        $this->campo[$num_campo]["tamano"] = $tamano;
        $this->campo[$num_campo]["tipo"] = $tipo;
        $this->campo[$num_campo]["funcion"] = $funcion;
        $this->campo[$num_campo]["campos_funcion"] = $campos_funcion;
    }


/******************************************************************************************
**  Se envia el código de los registros que se van a resaltar en la tabla.               **
**  Requiere:                                                                            **
**    - $codigo  Id del registro que se va a resaltar                                    **
**    - $estilo  (opt) Estilo con el que se mostrará el registro, por ejemplo:           **
**               "class='xxx'" ó "style='...'"                                           **
**                                                                                       **
**  Retorna:                                                                             **
**    Almacena los campos en el arreglo $this->resaltado                                 **
******************************************************************************************/
    function resaltar_fila($codigo, $estilo="") {
        if ($estilo=="") $estilo = $this->display["resaltar"];
        $this->resaltado[$codigo] = "$estilo";
    }


/**************************************************************************************************************
**  Busca el o los padres de los registros que se van a mostrar                                              **
**  Requiere:                                                                                                **
**    - $condicion    Parametro en base al que se busca al padre, puede ser un campo de la tabla o un valor  **
**    - $condicion_extra    (opt) condicion adicional para filtrar los registros.                            **
**  Retorna:                                                                                                 **
**    Almacena los resultados en el arreglo $this->padre, si son varios se los separa con comas.             **
**************************************************************************************************************/
    function buscar_padre($condicion, $condicion_extra="") {
        if ($this->display["debug"]) echo "<br>Buscar Padre";

        $sql = "select $this->id_tabla as \"id\",".
               " coalesce(".$this->id_padre.",$condicion) as \"padre\",".
               " $condicion as \"condicion\"".
               " from ".$this->tabla.
               " where ";

        if (strtoupper($condicion) == strtoupper($this->id_tabla)) { // Si se evalua la recursividad como en la tabla dependencias
            $sql .= " coalesce($this->id_padre,$this->id_tabla)=$this->id_tabla ";
            if ($condicion_extra != "") $sql .= " and $condicion_extra ";
        } else { // Si se busca el padre de un registro en especifico
            $sql .= " $this->id_tabla=$condicion ";
        }


        if ($this->display["debug"]) echo "<br>$sql";
        $rs = $this->db->query($sql);
        if (!$rs) return;
        if (trim($rs->fields["PADRE"]) === trim($rs->fields["CONDICION"])) { // En caso de que se cumpla con la condición
            $this->padre = "";
            $coma = "";
            while (!$rs->EOF) {
                $this->padre .= $coma.trim($rs->fields["ID"]);
                $coma = ",";
                $rs->MoveNext();
            }
        } else { // En caso de que tenga más padres
            $sql = "select count($this->id_tabla) as num from $this->tabla where ".
                    $rs->fields["PADRE"] . "=$this->id_tabla and $condicion_extra";
            if ($this->display["debug"]) echo "<br>$sql";
            $rs2 = $this->db->query($sql);
            if (!$rs2) return;
            if ($rs2->fields["NUM"] == 0) {
                $this->padre = $condicion;
                return;
            }
            $this->buscar_padre(trim($rs->fields["PADRE"]),$condicion_extra);
        }
        return;
    }


/******************************************************************************************************
**  Arma el query en base al cual se mostrarán los registros.                                        **
**  Requiere:                                                                                        **
**    - $condicion    (opt) condicion adicional para filtrar los registros.                          **
**  Retorna:                                                                                         **
**    Almacena los resultados en el arreglo $this->sql.                                              **
******************************************************************************************************/
    function set_sql($condicion="") {
        $num_campo = count($this->campo);
        $sql = "select ";
        $coma = "";
        for ($i=0 ; $i<$num_campo ; ++$i) {
            $sql .= $coma . $this->campo[$i]["campo"] . " as \"" . $this->campo[$i]["alias"] . "\" ";
            if ($this->campo[$i]["tipo"] == "L") {
                $sql .= ", '" . $this->campo[$i]["funcion"] . 
                        "(\"'||" . str_replace(",", "||'\",\"'||", $this->campo[$i]["campos_funcion"]) . "||'\");'".
                        " as \"f". $this->campo[$i]["alias"] ."\" ";
            }
            $coma = ", ";
        }
        $sql .= ", " . $this->id_tabla . " as \"codigo\", " . $this->id_padre . " as \"padre\" ";
        $sql .= " from " . $this->tabla . " where 1=1 **DATO** "; 
        // Será necesario reemplazar **DATO** con una cadena que evalua con el id del registro o el id del padre dependiendo del nivel
        if ($condicion != "") $sql .= " and " . $condicion;
        $sql .= ' order by ' . $this->display['order'];
        $this->sql = $sql;
        return;
    }


    function listar_recursivo($codigo, $nivel) {
        if ($this->display["debug"]) echo "<br>listar_recursivo($codigo, $nivel)";
        $mostrar_img1 = "style='display:none'";
        $mostrar_img2 = "";
        $mostrar_tr = "";
        if ($nivel==0) {
            //$codigo = str_replace (",", "','", $codigo);
            $sql = str_replace("**DATO**", " and ".$this->id_tabla." in ($codigo) ",$this->sql);
            if (!$this->display["expander"]) {
                $mostrar_img1 = "";
                $mostrar_img2 = "style='display:none'";
            }
        } else {
            $sql = str_replace("**DATO**", " and ".$this->id_padre."=$codigo and ".$this->id_tabla." <>$codigo ",$this->sql);
            if (!$this->display["expander"]) {
                $mostrar_img1 = "";
                $mostrar_img2 = "style='display:none'";
                $mostrar_tr = "style='display:none'";
            }
        }
        if ($this->display["debug"]) echo "<br>$sql";
        $rs = $this->db->query($sql);
        $num_campo = count($this->campo);
        $padre = $codigo;
        if ($nivel!=0) $this->variables_jsc .= "var REC_$padre = new Array();";
        if (!$rs or $rs->EOF) return;
        while (!$rs->EOF) {
            $codigo = $rs->fields["CODIGO"];
            if ($nivel!=0) $this->variables_jsc .= "REC_$padre.push('$codigo'); ";


            $imagenes = "<a href=\"javascript:;\" onclick=\"mostrar_ocultar_filas('$codigo')\">".
                        "<img src='".$this->db->rutaRaiz."/imagenes/add.png' id='img1_$codigo' $mostrar_img1>".
                        "<img src='".$this->db->rutaRaiz."/imagenes/menos.png' id='img2_$codigo' $mostrar_img2></a>";
            $espacios = str_repeat("&nbsp;",$nivel*$this->tabulacion) . "&nbsp;$imagenes&nbsp;";
            $estilo = "class='listado1'";
            if (isset($this->resaltado[$codigo])) $estilo = $this->resaltado[$codigo];
            $this->respuesta .= "\n<tr id='tr_rec_$codigo' $estilo $mostrar_tr>";
            for ($i=0 ; $i<$num_campo ; ++$i) {
                $campo = $rs->fields[strtoupper($this->campo[$i]["alias"])];
                if ($this->campo[$i]["tipo"] == "L") {
                    //cambio por link oculto
                    //$campo = "<a href='javascript:".$rs->fields[strtoupper("f".$this->campo[$i]["alias"])]."' ".
                      //       $this->display["link"] . ">$campo</a>";                    
                    $campo = '<a href="javascript:;" onclick='.$rs->fields[strtoupper('f'.$this->campo[$i]['alias'])].' '. $this->display["link"].' > '.$campo.'</a>';
                    
                }
                $this->respuesta .= "<td>$espacios $campo</td>";
                $espacios = "";
            }
            $this->respuesta .= "</tr>";
            $this->listar_recursivo($codigo, $nivel+1);
            $rs->MoveNext();
        }
        return;
    }

    function generar_tabla_recursiva() {
        if (trim($this->sql) == "") $this->set_sql($this->condicion);
        $num_campo = count($this->campo);
        $this->respuesta = "\n<table ". $this->display["tabla"] . "><tr>";
        for ($i=0 ; $i<$num_campo ; ++$i) {
            $tamano = $this->campo[$i]["tamano"];
            $campo = $this->campo[$i]["nombre"];
            $this->respuesta .= "<th width='$tamano%'><center>$campo</center></th>";
        }
        $this->respuesta .= "</tr>";
        $this->listar_recursivo($this->padre, 0);
        $this->respuesta .= "</table>";

        $tabla  = "<script>" . $this->variables_jsc . "</script>";
        $tabla .= $this->imprimir_funciones_javascript();
        $tabla .= $this->respuesta;
        return $tabla;
    }

    function imprimir_funciones_javascript() {
        $cad = "<script>".
                 "function mostrar_ocultar_filas(codigo) { ".
                     " if (document.getElementById('img1_'+codigo).style.display=='') { ".
                         " mostrar_ocultar_filas_recursivo(codigo, 1);".
                         " document.getElementById('img1_'+codigo).style.display = 'none';".
                         " document.getElementById('img2_'+codigo).style.display = '';".
                     "} else {".
                         " mostrar_ocultar_filas_recursivo(codigo, 0);".
                         " document.getElementById('img1_'+codigo).style.display = '';".
                         " document.getElementById('img2_'+codigo).style.display = 'none';".
                     "} ".
                 "} \n".
                 "function mostrar_ocultar_filas_recursivo(codigo_ori, accion) { ".
                     " var i = 0; ".
                     "for (i=0 ; i<window['REC_'+codigo_ori].length ; i++) {".
                         " codigo = window['REC_'+codigo_ori][i];".
                         " if (accion==1) { ".
                             " document.getElementById('tr_rec_'+codigo).style.display = '';".
                             " if (document.getElementById('img1_'+codigo).style.display=='none') { ".
                                 " mostrar_ocultar_filas_recursivo(codigo, accion);".
                             " } ".
                         "} else {".
                             " document.getElementById('tr_rec_'+codigo).style.display = 'none';".
                             " mostrar_ocultar_filas_recursivo(codigo, accion);".
                         "} ".
                     "} ".
                     "return; ".
                 "} ".
             "</script>";
/*
                     " if (document.getElementById('div_rec_'+codigo).innerHTML=='') { ".
                         " document.getElementById('div_rec_'+codigo).innerHTML = '<tr><td>Hola</td><td>Hola</td><td>Hola</td></tr>';".
                     "}".
/* */
        return $cad;
    }

    function asociar_registros($padre, $hijo) {
        $flag_loop = $this->validar_loop($padre, $hijo);
        if ($flag_loop) {
            $sql = "update $this->tabla set $this->id_padre=$padre where $this->id_tabla=$hijo";
            if ($this->display["debug"]) echo "<br>$sql";
            $this->db->query($sql);
            return 1;
        } else {
            return 0;
        }
    }

    function validar_loop($padre, $hijo) {
        $sql = "select $this->id_tabla as \"id\",".
               " coalesce(".$this->id_padre.",$padre) as \"id_padre\",".
               " $padre as \"padre\", ".
               " $hijo as \"hijo\"".
               " from ".$this->tabla.
               " where $this->id_tabla=$padre ";

        if ($this->display["debug"]) echo "<br>$sql";
        $rs = $this->db->query($sql);
        if (!$rs) return;

        if (trim($rs->fields["ID_PADRE"]) === trim($rs->fields["HIJO"])) return 0;
        if (trim($rs->fields["ID"]) === trim($rs->fields["HIJO"])) return 0;
        if (trim($rs->fields["ID_PADRE"]) === trim($rs->fields["PADRE"])) return 1;
        return $this->validar_loop(trim($rs->fields["ID_PADRE"]),$hijo);
    }

}


/*
///////  EJEMPLO  ///////

include "$ruta_raiz/recursivas/funciones_recursivas.php";
$lista = new FuncionesRecursivas($db);
$lista->tabla = "radicado";
$lista->id_tabla = "radi_nume_radi";
$lista->id_padre = "radi_nume_deri";
$lista->buscar_padre($radi_nume);
//$lista->buscar_padre("radi_nume_radi");
$lista->add_campo("No. Documento", "30", "radi_nume_text");
$lista->add_campo("Fecha", "20", "substr(radi_fech_ofic,1,19)||' GMT -5'", "L","mostrar_documento","radi_nume_radi,radi_nume_text");
$lista->add_campo("Asunto", "50", "radi_asunto");
$lista->resaltar_fila($radi_nume);
$lista->generar_tabla_recursiva();


/* */
?>