///*
// * Firma Digital: Cliente
// * This program is free software: you can redistribute it and/or modify
// * it under the terms of the GNU General Public License as published by
// * the Free Software Foundation, either version 3 of the License, or
// * (at your option) any later version.
// *
// * This program is distributed in the hope that it will be useful,
// * but WITHOUT ANY WARRANTY; without even the implied warranty of
// * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// * GNU General Public License for more details.
// *
// * You should have received a copy of the GNU General Public License
// * along with this program.  If not, see <http://www.gnu.org/licenses/>.
// */
package ec.gob.firmadigital.servicio.util;

import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.google.gson.JsonSyntaxException;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;

public class JsonProcessor {

    /**
     * Base 64 decoder
     */
    private static final Base64.Decoder BASE64_DECODER = Base64.getDecoder();

    /**
     * Base 64 encoder
     */
    private static final Base64.Encoder BASE64_ENCODER = Base64.getEncoder();

    /**
     * Transforma una cadena de texto JSON con documentos en Base 64 en un Map
     * de archivos binarios.
     */
    public static Map<Long, byte[]> parseJsonDocumentos(String json) {
        Map<Long, byte[]> documentosDecoder = new HashMap<>();
        try {
            JsonObject jsonObject = new JsonParser().parse(json).getAsJsonObject();
            JsonArray documentos = jsonObject.get("documentos").getAsJsonArray();
            Map<Long, String> documentosBase64 = new HashMap<>();
            for (int i = 0; i < documentos.size(); i++) {
                Long id = documentos.get(i).getAsJsonObject().get("id").getAsLong();
                String documento = documentos.get(i).getAsJsonObject().get("documento").getAsString();
                documentosBase64.put(id, documento);
            }
            // Documentos a retornar
            documentosDecoder = new HashMap<>();
            for (Long id : documentosBase64.keySet()) {
                String base64 = documentosBase64.get(id);
                documentosDecoder.put(id, BASE64_DECODER.decode(base64));
            }
        } catch (JsonSyntaxException e) {
            e.printStackTrace();
        }
        return documentosDecoder;
    }

    public static String parseJsonFechaHora(String json) {
        JsonObject jsonObject = new JsonParser().parse(json).getAsJsonObject();
        return jsonObject.get("fecha_hora").getAsString();
    }

    public static String parseJsonDocumentoFirmado(String json) {
        try {
            JsonObject jsonObject = new JsonParser().parse(json).getAsJsonObject();
            int documentosRecibidos = jsonObject.get("documentos_recibidos").getAsInt();
            int documentosFirmados = jsonObject.get("documentos_firmados").getAsInt();
            if (documentosFirmados == documentosRecibidos
                    || (documentosFirmados > 0 && documentosFirmados < documentosRecibidos)) {
                return "Se firmó exitosamente " + documentosFirmados + " documento(s) de " + documentosRecibidos;
            } else {
                return "No se firmaron los documentos";
            }
        } catch (JsonSyntaxException e) {
            e.printStackTrace();
            return "Problemas con la respuesta desde el servidor";
        }
    }

    /**
     * Crear una cadena de texto JSON para representar documentos en Base 64.
     */
    public static String buildJson(Map<Long, byte[]> documentos, String cedula) {
        // Documentos a retornar
        JsonArray jsonArray = new JsonArray();
        JsonObject jsonObject = new JsonObject();
        JsonObject documentosEncoder = null;
        for (Long id : documentos.keySet()) {
            documentosEncoder = new JsonObject();
            byte[] documento = documentos.get(id);
            String base64 = BASE64_ENCODER.encodeToString(documento);
            documentosEncoder.addProperty("id", id);
            documentosEncoder.addProperty("documento", base64);
            jsonArray.add(documentosEncoder);
        }
        jsonObject.addProperty("cedula", cedula);
        jsonObject.add("documentos", new com.google.gson.JsonParser()
                .parse(new com.google.gson.Gson().toJson(jsonArray)).getAsJsonArray());
        return jsonObject.toString();
    }
    
    /**
     * Crear una cadena de texto JSON con información del API para autorizar.
     */
    public static String buildJsonApi(String sistema, String url) {
        JsonObject jsonObject = new JsonObject();
        jsonObject.addProperty("sistema", sistema);
        jsonObject.addProperty("url", url);
        return jsonObject.toString();
    }
}
