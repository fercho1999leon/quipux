/*
 * Firma Digital: API
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
package ec.gob.firmadigital.api;

import static org.junit.Assert.assertNotNull;

import java.time.Instant;
import java.time.format.DateTimeFormatter;
import java.time.temporal.TemporalAccessor;
import java.util.Date;
import java.util.logging.Logger;

import org.junit.Test;

/**
 * Prueba para ServicioFechaHora
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
public class ServicioFechaHoraTest {

    private static final Logger logger = Logger.getLogger(ServicioFechaHoraTest.class.getName());

    @Test
    public void testFechaHora() throws Exception {
//        ServicioFechaHora servicioFechaHora = new ServicioFechaHora();
//        String fechaHora = servicioFechaHora.getFechaHora("");
//        logger.info("fechaHora=" + fechaHora);
//
//        DateTimeFormatter timeFormatter = DateTimeFormatter.ISO_OFFSET_DATE_TIME;
//        TemporalAccessor accessor = timeFormatter.parse(fechaHora);
//        Date date = Date.from(Instant.from(accessor));
//        logger.info("date=" + date);
//        assertNotNull(date);
    }
}
