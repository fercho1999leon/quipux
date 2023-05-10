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

import com.google.gson.Gson;
import com.google.gson.JsonObject;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;
import javax.ws.rs.Consumes;
import javax.ws.rs.FormParam;
import javax.ws.rs.NotFoundException;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.client.Client;
import javax.ws.rs.client.ClientBuilder;
import javax.ws.rs.client.Entity;
import javax.ws.rs.client.Invocation;
import javax.ws.rs.client.WebTarget;
import javax.ws.rs.core.Form;
import javax.ws.rs.core.MediaType;

/**
 * Este servicio permite obtener la fecha y hora del servidor en formato
 * ISO-8601.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Path("/fecha-hora")
public class ServicioFechaHora {

    // Servicio REST interno
    private static final String REST_SERVICE_URL = "https://ws.firmadigital.gob.ec/servicio/version";
//    private static final String REST_SERVICE_URL = "http://impws.firmadigital.gob.ec:8080/servicio/version";
//    private static final String REST_SERVICE_URL = "http://localhost:8080/servicio/version";

    /**
     * Retorna la fecha y hora del servidor, en formato ISO-8601.Por ejemplo:
     * "2017-08-27T17:54:43.562-05:00"
     *
     * @param base64
     * @return
     */
    @POST
    @Consumes(MediaType.APPLICATION_FORM_URLENCODED)
    @Produces(MediaType.TEXT_PLAIN)
    public String getFechaHora(@FormParam("base64") String base64) {
        try {
            String respuesta = buscarVersion(base64);
            
            String resultado;
            try {
                JsonObject jsonObject = new Gson().fromJson(respuesta, JsonObject.class);
                resultado = jsonObject.get("resultado").getAsString();
            } catch (NullPointerException | com.google.gson.JsonSyntaxException e) {
                return null;
            }

            if (resultado.equals("Version enabled")) {
                return ZonedDateTime.now().format(DateTimeFormatter.ISO_OFFSET_DATE_TIME);
            } else {
                return null;
            }
        } catch (NotFoundException e) {
            return null;
//            return "No se encuentra el servidor de búsqueda";
        }
    }

    private String buscarVersion(String base64) throws NotFoundException {
        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(REST_SERVICE_URL);
        Invocation.Builder builder = target.request();
        Form form = new Form();
        form.param("base64", base64);
        Invocation invocation = builder.buildPost(Entity.form(form));
        return invocation.invoke(String.class);
    }
}
