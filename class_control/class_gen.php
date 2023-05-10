<?
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
 * CLASS_GEN es la clase que obtiene un string con una fecha (mm/dd/yyy) y lo convierte en diferentes formatos
 * @author      Sixto Angel Pinz�n
 * @author      Jairo Hernan Losada
 * @version     1.0
 */

class CLASS_GEN
{
// traducefecha.php 
// 14 de Octubre de 2003 
// Traduce una fecha en formato mm/dd/yyy a formato texto en castellano 
// Desde la pagina llamaremos a la funcion 
// include("traducefecha.php"); 
// echo traducefecha("11/15/2003"); Visualiza la fecha 
// Donde la fecha ponemos la variable que queremos traducir en formato mm/dd/yyyy 
// 
function traducefecha($fecha) 
    { 
    if (strlen(trim($fecha))==0)	
    	return ("<NO ESPECIFICADA>");
    
    
    $fecha= strtotime($fecha); // convierte la fecha de formato mm/dd/yyyy a marca de tiempo 
    $diasemana=date("w", $fecha);// optiene el n�mero del dia de la semana. El 0 es domingo 
       switch ($diasemana) 
       { 
       case "0": 
          $diasemana="Domingo"; 
          break; 
       case "1": 
          $diasemana="Lunes"; 
          break; 
       case "2": 
          $diasemana="Martes"; 
          break; 
       case "3": 
          $diasemana="Miércoles"; 
          break; 
       case "4": 
          $diasemana="Jueves"; 
          break; 
       case "5": 
          $diasemana="Viernes"; 
          break; 
       case "6": 
          $diasemana="Sábado"; 
          break; 
       } 
    $dia=date("d",$fecha); // d�a del mes en n�mero 
    $mes=date("m",$fecha); // n�mero del mes de 01 a 12 
       switch($mes) 
       { 
       case "01": 
          $mes="enero";
          break; 
       case "02": 
          $mes="febrero";
          break; 
       case "03": 
          $mes="marzo";
          break; 
       case "04": 
          $mes="abril";
          break; 
       case "05": 
          $mes="mayo";
          break; 
       case "06": 
          $mes="junio";
          break; 
       case "07": 
          $mes="julio";
          break; 
       case "08": 
          $mes="agosto";
          break; 
       case "09": 
          $mes="septiembre";
          break; 
       case "10": 
          $mes="octubre";
          break; 
       case "11": 
          $mes="noviembre";
          break; 
       case "12": 
          $mes="diciembre";
          break; 
       } 
    $ano=date("Y",$fecha); // optenemos el a�o en formato 4 digitos 
    //$fecha= $diasemana.", ".$dia." de ".$mes." de ".$ano; // unimos el resultado en una unica cadena
    $fecha= $dia." de ".$mes." de ".$ano; // unimos el resultado en una unica cadena
    return $fecha; //enviamos la fecha al programa 
    }
	
	
	function traducefecha_sinDia($fecha) 
    { 
    
    	
    if (strlen(trim($fecha))==0)
    	return ("<NO ESPECIFICADA>");
    
    		
    $fecha= strtotime($fecha); // convierte la fecha de formato mm/dd/yyyy a marca de tiempo 
    
     
    $dia=date("d",$fecha); // d�a del mes en n�mero 
    $mes=date("m",$fecha); // n�mero del mes de 01 a 12 
       switch($mes) 
       { 
       case "01": 
          $mes="Enero"; 
          break; 
       case "02": 
          $mes="Febrero"; 
          break; 
       case "03": 
          $mes="Marzo"; 
          break; 
       case "04": 
          $mes="Abril"; 
          break; 
       case "05": 
          $mes="Mayo"; 
          break; 
       case "06": 
          $mes="Junio"; 
          break; 
       case "07": 
          $mes="Julio"; 
          break; 
       case "08": 
          $mes="Agosto"; 
          break; 
       case "09": 
          $mes="Septiembre"; 
          break; 
       case "10": 
          $mes="Octubre"; 
          break; 
       case "11": 
          $mes="Noviembre"; 
          break; 
       case "12": 
          $mes="Diciembre"; 
          break; 
       }
			 
		      switch($dia) 
       { 
       case "1": 
          $dia="un"; 
          break; 
       case "2": 
          $dia="dos"; 
          break; 
       case "3": 
          $dia="tres"; 
          break; 
       case "4": 
          $dia="cuatro"; 
          break; 
       case "5": 
          $dia="cinco"; 
          break; 
       case "6": 
          $dia="seis"; 
          break; 
       case "7": 
          $dia="siete"; 
          break; 
       case "8": 
          $dia="ocho"; 
          break; 
       case "9": 
          $dia="nueve"; 
          break; 
       case "10": 
          $dia="diez"; 
          break; 
       case "11": 
          $dia="once"; 
          break; 
       case "12": 
          $dia="doce"; 
          break; 
			case "13": 
          $dia="trece"; 
          break; 
			case "14": 
          $dia="catorce"; 
          break;
			case "15": 
          $dia="quince"; 
          break; 
			case "16": 
          $dia="dieciseis"; 
          break; 
       case "17": 
          $dia="diecisiete"; 
          break; 
       case "18": 
          $dia="dieciocho"; 
          break; 
       case "19": 
          $dia="dicinueve"; 
          break; 
       case "20": 
          $dia="veinte"; 
          break; 
       case "21": 
          $dia="veintiun"; 
          break; 
       case "22": 
          $dia="veintidos"; 
          break; 
       case "23": 
          $dia="veintitres"; 
          break; 
       case "24": 
          $dia="veinticuatro"; 
          break; 
       case "25": 
          $dia="veinticinco"; 
          break; 
       case "26": 
          $dia="veintiseis"; 
          break; 
       case "27": 
          $dia="veintisiete"; 
          break; 
			case "28": 
          $dia="veintiocho"; 
          break; 
			case "29": 
          $dia="veintiueve"; 
          break;
			case "30": 
          $dia="treinta"; 
          break; 
			case "31": 
          $dia="treinta y un"; 
          break; 
			
					
					
       }	  
    $ano=date("Y",$fecha); // optenemos el a�o en formato 4 digitos 
    $fecha= $dia." día(s) del mes de ".$mes." de ".$ano; // unimos el resultado en una unica cadena 
    return $fecha; //enviamos la fecha al programa 
    }

		function traducefechaDocto($fecha) 
    { 
    	
    if (strlen(trim($fecha))==0)	
    	return ("<NO ESPECIFICADA>");
    	
  
    		
    $fecha= strtotime(trim($fecha)); // convierte la fecha de formato mm/dd/yyyy a marca de tiempo 
    
     
    $dia=date("d",$fecha); // d�a del mes en n�mero 
    $mes=date("m",$fecha); // n�mero del mes de 01 a 12 
       switch($mes) 
       { 
       case "01": 
          $mes="Enero"; 
          break; 
       case "02": 
          $mes="Febrero"; 
          break; 
       case "03": 
          $mes="Marzo"; 
          break; 
       case "04": 
          $mes="Abril"; 
          break; 
       case "05": 
          $mes="Mayo"; 
          break; 
       case "06": 
          $mes="Junio"; 
          break; 
       case "07": 
          $mes="Julio"; 
          break; 
       case "08": 
          $mes="Agosto"; 
          break; 
       case "09": 
          $mes="Septiembre"; 
          break; 
       case "10": 
          $mes="Octubre"; 
          break; 
       case "11": 
          $mes="Noviembre"; 
          break; 
       case "12": 
          $mes="Diciembre"; 
          break; 
       }
			 
		      switch($dia) 
       { 
       case "1": 
          $dia="un"; 
          break; 
       case "2": 
          $dia="dos"; 
          break; 
       case "3": 
          $dia="tres"; 
          break; 
       case "4": 
          $dia="cuatro"; 
          break; 
       case "5": 
          $dia="cinco"; 
          break; 
       case "6": 
          $dia="seis"; 
          break; 
       case "7": 
          $dia="siete"; 
          break; 
       case "8": 
          $dia="ocho"; 
          break; 
       case "9": 
          $dia="nueve"; 
          break; 
       case "10": 
          $dia="diez"; 
          break; 
       case "11": 
          $dia="once"; 
          break; 
       case "12": 
          $dia="doce"; 
          break; 
			case "13": 
          $dia="trece"; 
          break; 
			case "14": 
          $dia="catorce"; 
          break;
			case "15": 
          $dia="quince"; 
          break; 
			case "16": 
          $dia="dieciseis"; 
          break; 
       case "17": 
          $dia="diecisiete"; 
          break; 
       case "18": 
          $dia="dieciocho"; 
          break; 
       case "19": 
          $dia="dicinueve"; 
          break; 
       case "20": 
          $dia="veinte"; 
          break; 
       case "21": 
          $dia="veintiuno"; 
          break; 
       case "22": 
          $dia="veintidos"; 
          break; 
       case "23": 
          $dia="veintitres"; 
          break; 
       case "24": 
          $dia="veinticuatro"; 
          break; 
       case "25": 
          $dia="veinticinco"; 
          break; 
       case "26": 
          $dia="veintiseis"; 
          break; 
       case "27": 
          $dia="veintisiete"; 
          break; 
			case "28": 
          $dia="veintiocho"; 
          break; 
			case "29": 
          $dia="veintiueve"; 
          break;
			case "30": 
          $dia="treinta"; 
          break; 
			case "31": 
          $dia="treinta y uno"; 
          break; 
			
					
					
       }	  
    $ano=date("Y",$fecha); // optenemos el a�o en formato 4 digitos 
    $fecha= $dia." de ".$mes." de ".$ano; // unimos el resultado en una unica cadena 
    return $fecha; //enviamos la fecha al programa 
    }

		
		
	
		
	
	}

   ?>