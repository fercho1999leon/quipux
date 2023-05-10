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

import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.google.gson.JsonSyntaxException;
import ec.gob.firmadigital.servicio.ServicioAppFirmarDocumentoTransversal;
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
@Path("/appfirmardocumentotransversal")
public class ServicioAppFirmarDocumentoTransversalRest {

    @EJB
    private ServicioAppFirmarDocumentoTransversal servicioAppFirmarDocumentoTransversal;

    @POST
    @Produces(MediaType.TEXT_PLAIN)
    @Consumes(MediaType.APPLICATION_FORM_URLENCODED)
    public String firmarDocumentoTransversal(@FormParam("pkcs12") String pkcs12, @FormParam("password") String password, @FormParam("json") String json, @FormParam("base64") String base64) throws Exception {

        if (pkcs12 == null || pkcs12.isEmpty()) {
            return "Se debe incluir el parametro pkcs12";
        }

        if (password == null || password.isEmpty()) {
            return "Se debe incluir el parametro password";
        }

        if (json == null || json.isEmpty()) {
            return "Se debe incluir el parametro json";
        }

        JsonObject jsonObject;
        try {
            jsonObject = new JsonParser().parse(json).getAsJsonObject();
        } catch (JsonSyntaxException e) {
            return getClass().getSimpleName() + "::Error al decodificar JSON: \"" + e.getMessage();
        }

        String sistema = null;
        String operacion = null;
        String url = null;
        String versionFirmaEC = null;
        String formatoDocumento = null;
        String tokenJwt = null;
        String llx = null;
        String lly = null;
        String pagina = null;
        String tipoEstampado = null;
        String razon = null;
        boolean pre = false;
        boolean des = false;

        try {
            sistema = jsonObject.get("sistema").getAsString();
        } catch (NullPointerException npe) {
            return "Error al decodificar JSON: Se debe incluir \"sistema\"";
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"sistema\"";
        }
        try {
            operacion = jsonObject.get("operacion").getAsString();
        } catch (NullPointerException npe) {
            return "Error al decodificar JSON: Se debe incluir \"operacion\"";
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"operacion\"";
        }
        try {
            if (jsonObject.get("url") != null) {
                url = jsonObject.get("url").getAsString();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"url\"";
        }
        try {
            versionFirmaEC = jsonObject.get("versionFirmaEC").getAsString();
        } catch (NullPointerException npe) {
            return "Error al decodificar JSON: Se debe incluir \"versionFirmaEC\"";
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"versionFirmaEC\"";
        }
        try {
            formatoDocumento = jsonObject.get("formatoDocumento").getAsString();
        } catch (NullPointerException npe) {
            formatoDocumento = "pdf";
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"formatoDocumento\"";
        }
        try {
            tokenJwt = jsonObject.get("tokenJwt").getAsString();
        } catch (NullPointerException npe) {
            return "Error al decodificar JSON: Se debe incluir \"tokenJwt\"";
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"tokenJwt\"";
        }
        try {
            if (jsonObject.get("llx") != null) {
                llx = jsonObject.get("llx").getAsString();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"llx\"";
        }
        try {
            if (jsonObject.get("lly") != null) {
                lly = jsonObject.get("lly").getAsString();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"lly\"";
        }
        try {
            if (jsonObject.get("pagina") != null) {
                pagina = jsonObject.get("pagina").getAsString();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"pagina\"";
        }
        try {
            if (jsonObject.get("tipoEstampado") != null) {
                tipoEstampado = jsonObject.get("tipoEstampado").getAsString();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"tipoEstampado\"";
        }
        try {
            if (jsonObject.get("razon") != null) {
                razon = jsonObject.get("razon").getAsString();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"razon\"";
        }
        try {
            if (jsonObject.get("pre") != null) {
                pre = jsonObject.get("pre").getAsBoolean();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"pre\"";
        }
        try {
            if (jsonObject.get("des") != null) {
                des = jsonObject.get("des").getAsBoolean();
            }
        } catch (ClassCastException cce) {
            return "Error al decodificar JSON: No coincide el tipo de dato \"des\"";
        }

        if (base64 == null || base64.isEmpty()) {
            return "Se debe incluir el parametro base64";
        }
        
        return servicioAppFirmarDocumentoTransversal.firmarTransversal(pkcs12, password, sistema, operacion, url, versionFirmaEC, formatoDocumento, tokenJwt, llx, lly, pagina, tipoEstampado, razon, pre, des, base64);
    }

}
