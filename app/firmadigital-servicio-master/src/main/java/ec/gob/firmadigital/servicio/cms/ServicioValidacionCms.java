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
package ec.gob.firmadigital.servicio.cms;

import java.util.List;

import javax.ejb.Stateless;
import javax.json.Json;
import javax.json.JsonArrayBuilder;
import javax.json.JsonObjectBuilder;
import javax.ws.rs.Consumes;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;

import ec.gob.firmadigital.servicio.util.Base64InvalidoException;
import ec.gob.firmadigital.servicio.util.Base64Util;
import io.rubrica.exceptions.SignatureVerificationException;
import io.rubrica.certificate.to.DatosUsuario;
import io.rubrica.sign.cms.VerificadorCMS;

/**
 * Servicio de validacion de archivos CMS (P7M).
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
@Path("/validacioncms")
public class ServicioValidacionCms {

    @POST
    @Consumes(MediaType.TEXT_PLAIN)
    @Produces(MediaType.APPLICATION_JSON)
    public Response verificarCms(String archivoBase64) throws SignatureVerificationException {

        byte[] archivo;

        try {
            archivo = Base64Util.decode(archivoBase64);
        } catch (Base64InvalidoException e) {
            return Response.status(Status.BAD_REQUEST).entity("Error al decodificar Base64").build();
        }

        VerificadorCMS verificador = new VerificadorCMS();
        byte[] archivoOriginal = verificador.verify(archivo);
        String archivoOriginalBase64 = Base64Util.encode(archivoOriginal);

        JsonObjectBuilder objectBuilder = Json.createObjectBuilder();
        objectBuilder.add("archivo", archivoOriginalBase64);

        // Para construir un array de firmantes
        JsonArrayBuilder arrayBuilder = Json.createArrayBuilder();

        // FIXME
        List<DatosUsuario> listaDatosUsuario = verificador.listaDatosUsuario;

        for (DatosUsuario datosUsuario : listaDatosUsuario) {
            JsonObjectBuilder builder = Json.createObjectBuilder();
            builder.add("nombre", datosUsuario.getNombre());
            builder.add("apellido", datosUsuario.getApellido());
            builder.add("cargo", datosUsuario.getCargo());
            builder.add("cedula", datosUsuario.getCedula());
            builder.add("institucion", datosUsuario.getInstitucion());
            arrayBuilder.add(builder);
        }

        objectBuilder.add("firmantes", arrayBuilder.build());
        String json = objectBuilder.build().toString();

        return Response.ok(json, MediaType.APPLICATION_JSON).build();
    }
}
