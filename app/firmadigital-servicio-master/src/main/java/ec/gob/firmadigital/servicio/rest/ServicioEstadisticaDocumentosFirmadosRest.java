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

import ec.gob.firmadigital.servicio.ApiEstadisticaException;
import ec.gob.firmadigital.servicio.ServicioEstadisticaDocumentosFirmados;
import java.io.StringReader;
import javax.ejb.Stateless;
import javax.ejb.EJB;
import javax.json.Json;
import javax.json.JsonObject;
import javax.json.JsonReader;
import javax.json.stream.JsonParsingException;
import javax.ws.rs.Consumes;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;

/**
 * Buscar en una lista de URLs permitidos para utilizar como API. Esto permite
 * federar la utilización de FirmaEC sobre otra infraestructura, consultando en
 * una lista de servidores permitidos.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
@Path("/estadisticadocumentosfirmados")
public class ServicioEstadisticaDocumentosFirmadosRest {

    @EJB
    private ServicioEstadisticaDocumentosFirmados servicioEstadisticaDocumentosFirmados;

    @GET
    @Path("{json}")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.TEXT_PLAIN)
    public String buscarPorFechaDesdeFechaHasta(@PathParam("json") String jsonParameter) {
        if (jsonParameter == null || jsonParameter.isEmpty()) {
            return "Se debe incluir JSON con los parámetros: sistema, fecha_desde y fecha_hasta";
        }

        JsonReader jsonReader = Json.createReader(new StringReader(jsonParameter));
        JsonObject json;

        try {
            json = (JsonObject) jsonReader.read();
        } catch (JsonParsingException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: \"" + e.getMessage();
        }

        String sistema;
        String fechaDesde;
        String fechaHasta;

        try {
            sistema = json.getString("sistema");
        } catch (NullPointerException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: Se debe incluir \"sistema\"";
        }
        try {
            fechaDesde = json.getString("fecha_desde");
        } catch (NullPointerException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: Se debe incluir \"fecha_desde\"";
        }
        try {
            fechaHasta = json.getString("fecha_hasta");
        } catch (NullPointerException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: Se debe incluir \"fecha_hasta\"";
        }

        try {
            return servicioEstadisticaDocumentosFirmados.buscarPorFechaDesdeFechaHasta(sistema, fechaDesde, fechaHasta);
        } catch (ApiEstadisticaException e) {
            return "Url no encontrado";
        }
    }
}
