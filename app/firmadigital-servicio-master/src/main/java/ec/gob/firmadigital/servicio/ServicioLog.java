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
package ec.gob.firmadigital.servicio;

import javax.ejb.Stateful;
import javax.ejb.TransactionAttribute;
import javax.ejb.TransactionAttributeType;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

import ec.gob.firmadigital.servicio.model.Log;
import ec.gob.firmadigital.servicio.model.Log.Severidad;

/**
 * Servicio para almacenar, actualizar y obtener documentos desde los sistemas
 * transversales y la aplicación en firmadigital-api
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateful
public class ServicioLog {

    @PersistenceContext(unitName = "FirmaDigitalDS")
    private EntityManager em;

    @TransactionAttribute(TransactionAttributeType.REQUIRES_NEW)
    public void log(Severidad severidad, String categoria, String descripcion) {
        Log log = new Log(severidad, categoria, descripcion);
        em.persist(log);
    }

    public void info(String categoria, String descripcion) {
        log(Severidad.INFO, categoria, descripcion);
    }

    public void warning(String categoria, String descripcion) {
        log(Severidad.WARNING, categoria, descripcion);
    }

    public void error(String categoria, String descripcion) {
        log(Severidad.ERROR, categoria, descripcion);
    }
}
