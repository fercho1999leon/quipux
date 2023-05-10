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

import ec.gob.firmadigital.servicio.ServicioAppValidarCertificadoDigital;
import javax.ejb.EJB;
import javax.ejb.Stateless;
import javax.ws.rs.Produces;
import javax.ws.rs.Consumes;
import javax.ws.rs.FormParam;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.core.MediaType;

/**
 * REST Web Service
 *
 * @author Christian Espinosa <christian.espinosa@mintel.gob.ec>, Misael
 * Fernández
 */
@Stateless
@Path("/appvalidarcertificadodigital")
public class ServicioAppValidarCertificadoDigitalRest {

    @EJB
    private ServicioAppValidarCertificadoDigital servicioAppValidarCertificadoDigital;

    @POST
    @Produces(MediaType.APPLICATION_JSON)
    @Consumes(MediaType.APPLICATION_FORM_URLENCODED)
    public String validarCertificadoDigital(@FormParam("pkcs12") String pkcs12, @FormParam("password") String password, @FormParam("base64") String base64) {

        if (pkcs12 == null || pkcs12.isEmpty()) {
            return "Se debe incluir el parametro pkcs12";
        }

        if (password == null || password.isEmpty()) {
            return "Se debe incluir el parametro password";
        }
        
        if (base64 == null || base64.isEmpty()) {
            return "Se debe incluir el parametro base64";
        }
        return servicioAppValidarCertificadoDigital.appValidarCertificadoDigital(pkcs12, password, base64);
    }

}
