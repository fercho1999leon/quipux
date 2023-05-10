/*
 * Firma Digital: Servicio
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package ec.gob.firmadigital.servicio.token;

import java.util.Date;
import java.util.Map;

/**
 * Servicio para gestionar tokens que permiten una comunicación confiable con
 * una aplicación externa.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
public interface ServicioToken {

    /**
     * Generar un token sin expiracion.
     *
     * @param parametros
     * @return
     */
    String generarToken(Map<String, Object> parametros);

    /**
     * Generar un token con tiempo de expiracion
     *
     * @param parametros
     * @param expiracion
     * @return
     */
    String generarToken(Map<String, Object> parametros, Date expiracion);

    /**
     * Analizar los contenidos de un token para sacar la información necesaria
     * para procesar un documento.
     *
     * @param token
     * @return
     * @throws TokenInvalidoException
     * @throws TokenExpiradoException
     */
    Map<String, Object> parseToken(String token) throws TokenInvalidoException, TokenExpiradoException;
}
