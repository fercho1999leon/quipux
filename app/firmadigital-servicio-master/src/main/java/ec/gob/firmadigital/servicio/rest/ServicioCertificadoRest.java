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
package ec.gob.firmadigital.servicio.rest;

import java.math.BigInteger;

import javax.ejb.EJB;
import javax.ejb.Stateless;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;

import ec.gob.firmadigital.servicio.crl.ServicioConsultaCrl;

/**
 * Este servicio permite verificar si un certificado está revocado.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
@Path("/certificado")
public class ServicioCertificadoRest {

    @EJB
    private ServicioConsultaCrl servicioCrl;

    @GET
    @Path("/revocado/{serial}")
    @Produces(MediaType.TEXT_PLAIN)
    public String validarCertificado(@PathParam("serial") BigInteger serial) {
        return Boolean.toString(servicioCrl.isRevocado(serial));
    }

    @GET
    @Path("/fechaRevocado/{serial}")
    @Produces(MediaType.TEXT_PLAIN)
    public String validarFechaRevocado(@PathParam("serial") BigInteger serial) {
        return servicioCrl.fechaRevocado(serial);
    }
}
