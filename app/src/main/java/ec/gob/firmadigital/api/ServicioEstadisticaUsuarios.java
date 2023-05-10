/*
 * Firma Digital: API
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
package ec.gob.firmadigital.api;

import java.util.logging.Logger;
import javax.ws.rs.Consumes;
import javax.ws.rs.GET;
import javax.ws.rs.NotFoundException;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.client.Client;
import javax.ws.rs.client.ClientBuilder;
import javax.ws.rs.client.Invocation;
import javax.ws.rs.client.WebTarget;
import javax.ws.rs.core.MediaType;

/**
 * Permite obtener estadisticas.
 *
 * @author mfernandez
 */
@Path("/estadisticausuarios")
public class ServicioEstadisticaUsuarios {

    // Servicio REST interno
    private static final String REST_SERVICE_URL = "https://ws.firmadigital.gob.ec/servicio/estadisticausuarios";
//    private static final String REST_SERVICE_URL = "http://impws.firmadigital.gob.ec:8080/servicio/estadisticausuarios";
//    private static final String REST_SERVICE_URL = "http://localhost:8080/servicio/estadisticausuarios";

    private static final Logger logger = Logger.getLogger(ServicioEstadisticaUsuarios.class.getName());

    @GET
    @Path("{json}")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.TEXT_PLAIN)
    public String validarEndpoint(@PathParam("json") String jsonParameter) {
        logger.info("json=" + jsonParameter);
        try {
            return buscarPorFechaDesdeFechaHasta(jsonParameter);
        } catch (NotFoundException e) {
            return "No se encuentra el servidor de búsqueda";
        }
    }

    private String buscarPorFechaDesdeFechaHasta(String json) throws NotFoundException {
        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(REST_SERVICE_URL).path("{json}").resolveTemplate("json", json);
        Invocation.Builder builder = target.request();
        Invocation invocation = builder.buildGet();
        return invocation.invoke(String.class);
    }
}
