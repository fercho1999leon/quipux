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

/**
 * Timeout para un token.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
public class TokenTimeout {

    /**
     * Minutos antes de que el Token expire.
     */
    public static final int DEFAULT_TIMEOUT = 5;

    /**
     * Agregar una cantidad de minutos a una hora dada.
     *
     * @param date
     * @param minutes
     * @return
     */
    public static Date addMinutes(Date date, int minutes) {
        long time = date.getTime() + (minutes * 60 * 1000);
        return new Date(time);
    }
}
