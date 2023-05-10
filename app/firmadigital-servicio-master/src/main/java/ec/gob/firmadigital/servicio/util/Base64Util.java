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
package ec.gob.firmadigital.servicio.util;

import java.util.Base64;
import java.util.Base64.Decoder;
import java.util.Base64.Encoder;

/**
 * Clase utilitaria para procesar formato Base64.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
public class Base64Util {

    private static final Decoder DECODER = Base64.getDecoder();
    private static final Encoder ENCODER = Base64.getEncoder();

    public static byte[] decode(String base64) throws Base64InvalidoException {
        try {
            return DECODER.decode(base64);
        } catch (IllegalArgumentException e) {
            throw new Base64InvalidoException(e);
        }
    }

    public static String encode(byte[] data) {
        return ENCODER.encodeToString(data);
    }
}
