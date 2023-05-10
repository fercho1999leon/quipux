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

import ec.gob.firmadigital.servicio.ApiUrlNoEncontradoException;
import javax.ejb.EJB;
import javax.ejb.Stateless;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import ec.gob.firmadigital.servicio.ServicioApiUrl;
import java.io.StringReader;
import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;
import java.util.Base64;
import java.util.logging.Logger;
import javax.json.Json;
import javax.json.JsonReader;
import javax.json.stream.JsonParsingException;

/**
 * Servicio REST para verificar si existe un API URL.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
@Path("/apiurl")
public class ServicioApiUrlRest {

    private static final Logger logger = Logger.getLogger(ServicioApiUrlRest.class.getName());
    @EJB
    private ServicioApiUrl servicioApiUrl;

    @GET
    @Path("{base64}")
    @Produces(MediaType.TEXT_PLAIN)
    public String buscarUrl(@PathParam("base64") String base64) {
        if (base64 == null || base64.isEmpty()) {
            return "Se debe generar en Base64";
        }
        logger.info("URLBase64=" + base64);
        String jsonParameter = new String(Base64.getDecoder().decode(base64));
        if (jsonParameter == null || jsonParameter.isEmpty()) {
            return "Se debe incluir JSON con los parámetros: sistema, fecha_desde y fecha_hasta";
        }

        javax.json.JsonObject json;
        try {
            JsonReader jsonReader = Json.createReader(new StringReader(URLDecoder.decode(jsonParameter, "UTF-8")));
            json = (javax.json.JsonObject) jsonReader.read();
        } catch (JsonParsingException | UnsupportedEncodingException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: \"" + e.getMessage();
        }

        String sistema;
        String url;

        try {
            sistema = json.getString("sistema");
        } catch (NullPointerException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: Se debe incluir \"sistema\"";
        }
        try {
            url = json.getString("url");
        } catch (NullPointerException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: Se debe incluir \"url\"";
        }

        try {
            return servicioApiUrl.buscarPorUrl(sistema, url);
        } catch (ApiUrlNoEncontradoException e) {
            return "Url no encontrado";
        }
    }
}
