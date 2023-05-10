<?php
/** <Programa para el manejo de gestion documental, oficios, memorandus, circulares, acuerdos>
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
**/

//	 Session Encryption by Ari Kuorikoski <ari.kuorikoski@finebyte.com>
class MD5Crypt{
		function keyED($txt,$encrypt_key)
		{
				$encrypt_key = md5($encrypt_key);
				$ctr=0;
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++){
						if ($ctr==strlen($encrypt_key)) $ctr=0;
						$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
						$ctr++;
				}
				return $tmp;
		}

		function Encrypt($txt,$key)
		{
				srand((double)microtime()*1000000);
				$encrypt_key = md5(rand(0,32000));
				$ctr=0;
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++)
				{
				if ($ctr==strlen($encrypt_key)) $ctr=0;
				$tmp.= substr($encrypt_key,$ctr,1) .
				(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
				$ctr++;
				}
				return base64_encode($this->keyED($tmp,$key));
		}

		function Decrypt($txt,$key)
		{
				$txt = $this->keyED(base64_decode($txt),$key);
				$tmp = "";
				for ($i=0;$i<strlen($txt);$i++){
						$md5 = substr($txt,$i,1);
						$i++;
						$tmp.= (substr($txt,$i,1) ^ $md5);
				}
				return $tmp;
		}

		function RandPass()
		{
				$randomPassword = "";
				srand((double)microtime()*1000000);
				for($i=0;$i<8;$i++)
				{
						$randnumber = rand(48,120);

						while (($randnumber >= 58 && $randnumber <= 64) || ($randnumber >= 91 && $randnumber <= 96))
						{
								$randnumber = rand(48,120);
						}

						$randomPassword .= chr($randnumber);
				}
				return $randomPassword;
		}

}
?>