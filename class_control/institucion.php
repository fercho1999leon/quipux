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
 * Usuario es la clase encargada de gestionar las operaciones y los datos b�sicos referentes a un usuario
 * @author	Sixto Angel Pinz�n
 * @version	1.0
 */
class Institucion {
    /**
       * Gestor de las transacciones con la base de datos
       * @var ConnectionHandler
       * @access public
       */
    var $cursor;
    
    /**
    * Constructor encargado de obtener la conexion
    * @param	$db	ConnectionHandler es el objeto conexion
    * @return   void
    */
    function Institucion($db) {

        $this->cursor = & $db;

    }

    /**
     * Funcion para guardar la dependencia coordinadora
     */
    function institucionCoordinadora($param){
        $ruta_raiz = "../..";
        $datos =  explode(",",$param["data"]);

        include_once "$ruta_raiz/rec_session.php";
        $db = new ConnectionHandler("$ruta_raiz");

        $q="insert into institucion_coordinador (inst_codi_coor, inst_codi, inst_coor_fecha) values ($datos[0],$datos[1],CURRENT_DATE)";
        //var_dump($q);
        $rs = $db->conn->Execute($q);
        return($rs);
    }

    /**
     * Funcion para eliminar registro de dependencia coordinadora
     */
    function elim_institucionCoordinadora($param){
        $ruta_raiz = "../..";
        $datos =  explode(",",$param["data"]);

        include_once "$ruta_raiz/rec_session.php";
        $db = new ConnectionHandler("$ruta_raiz");

        $q="delete from institucion_coordinador where inst_coor_codi = $datos[0] ";
        //var_dump($q);
        $rs = $db->conn->Execute($q);
        return($rs);
    }
}

?>
