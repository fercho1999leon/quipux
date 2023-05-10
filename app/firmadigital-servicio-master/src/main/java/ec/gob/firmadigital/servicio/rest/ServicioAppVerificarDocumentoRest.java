/*
 * Firma Digital: Servicio
 *
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

import ec.gob.firmadigital.servicio.ServicioAppVerificarDocumento;
import javax.ejb.EJB;
import javax.ws.rs.Consumes;
import javax.ws.rs.FormParam;
import javax.ws.rs.Produces;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.core.MediaType;

/**
 * REST Web Service
 *
 * @author Christian Espinosa <christian.espinosa@mintel.gob.ec>, Misael
 * Fernández
 */
@Path("/appverificardocumento")
public class ServicioAppVerificarDocumentoRest {

    @EJB
    private ServicioAppVerificarDocumento servicioAppVerificarDocumento;

    @POST
    @Produces(MediaType.TEXT_PLAIN)
    @Consumes(MediaType.APPLICATION_FORM_URLENCODED)
    public String validarDocumento(@FormParam("documento") String documento, @FormParam("base64") String base64) throws Exception {
        if (documento == null || documento.isEmpty()) {
            return "Se debe incluir el parametro documento";
        }
        if (base64 == null || base64.isEmpty()) {
            return "Se debe incluir el parametro base64";
        }
        return servicioAppVerificarDocumento.verificarDocumento(documento, base64);
    }
}
