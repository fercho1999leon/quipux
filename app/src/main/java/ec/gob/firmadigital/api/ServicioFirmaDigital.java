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

import javax.ws.rs.BadRequestException;
import javax.ws.rs.Consumes;
import javax.ws.rs.FormParam;
import javax.ws.rs.GET;
import javax.ws.rs.PUT;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.WebApplicationException;
import javax.ws.rs.client.Client;
import javax.ws.rs.client.ClientBuilder;
import javax.ws.rs.client.Entity;
import javax.ws.rs.client.Invocation;
import javax.ws.rs.client.Invocation.Builder;
import javax.ws.rs.client.WebTarget;
import javax.ws.rs.core.Form;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;

/**
 * Servicio REST para utilizar desde la aplicación del lado del cliente.
 *
 * Es a su vez un cliente REST para invocar servicios provistos
 *
 * Este mecanismo permite invocar los servicios internos desde un cliente
 * externo.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Path("/firmadigital")
public class ServicioFirmaDigital {

    // Servicio REST interno
    private static final String REST_SERVICE_URL = "https://ws.firmadigital.gob.ec/servicio/documentos";
//    private static final String REST_SERVICE_URL = "http://impws.firmadigital.gob.ec:8080/servicio/documentos";
//    private static final String REST_SERVICE_URL = "http://localhost:8080/servicio/documentos";

    /**
     * Obterner un documento mediante una invocación REST
     *
     * @param token
     * @return
     */
    @GET
    @Path("{token}")
    @Produces(MediaType.APPLICATION_JSON)
    public Response obtenerDocumentos(@PathParam("token") String token) {
        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(REST_SERVICE_URL).path("{token}").resolveTemplate("token", token);
        Builder builder = target.request(MediaType.APPLICATION_JSON);
        Invocation invocation = builder.buildGet();

        try {
            String json = invocation.invoke(String.class);
            return Response.ok(json).header("Content-Length", json.length()).build();
        } catch (BadRequestException e) {
            String mensaje = e.getResponse().readEntity(String.class);
//            String mensaje;
//            if (e.getResponse().hasEntity()) {
//                mensaje = e.getResponse().readEntity(String.class);
//            } else {
//                mensaje = e.toString();
//            }
            return Response.status(Status.BAD_REQUEST).type(MediaType.TEXT_PLAIN).entity(mensaje).build();
        } catch (WebApplicationException e) {
            String mensaje = e.getResponse().readEntity(String.class);
//            String mensaje;
//            if (e.getResponse().hasEntity()) {
//                mensaje = e.getResponse().readEntity(String.class);
//            } else {
//                mensaje = e.toString();
//            }
            return Response.status(Status.INTERNAL_SERVER_ERROR).type(MediaType.TEXT_PLAIN).entity(
                    "Error al invocar servicio de obtencion de documentos en firmadigital-servicio: " + mensaje)
                    .build();
        }
    }

    /**
     * Actualizar un documento mediante una invocación REST
     *
     * @param token
     * @param json
     * @param base64
     * @return
     */
    @PUT
    @Path("{token}")
    @Consumes(MediaType.APPLICATION_FORM_URLENCODED)
    public Response actualizarDocumentos(@PathParam("token") String token, @FormParam("json") String json, @FormParam("base64") String base64) {
        if (json == null) {
            return Response.status(Status.BAD_REQUEST).entity("Se debe incluir json").build();
        }
        if (base64 == null) {
            return Response.status(Status.BAD_REQUEST).entity("Se debe incluir base64").build();
        }

        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(REST_SERVICE_URL).path("{token}").resolveTemplate("token", token);
        Builder builder = target.request();
        Form form = new Form();
        form.param("json", json);
        form.param("base64", base64);
        Invocation invocation = builder.buildPut(Entity.form(form));

        try {
            String jsonResponse = invocation.invoke(String.class);
            return Response.ok(jsonResponse).header("Content-Length", jsonResponse.length()).build();
        } catch (BadRequestException e) {
            return Response.status(Status.BAD_REQUEST).type(MediaType.TEXT_PLAIN).entity(e.getResponse().readEntity(String.class)).build();
        } catch (WebApplicationException e) {
            return Response.status(Status.INTERNAL_SERVER_ERROR).type(MediaType.TEXT_PLAIN).entity(
                    "Error al invocar servicio de obtencion de documentos en firmadigital-servicio: " + e.getResponse().readEntity(String.class))
                    .build();
        }
    }
}
