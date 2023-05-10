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
package ec.gob.firmadigital.servicio;

import com.itextpdf.kernel.pdf.PdfReader;
import static ec.gob.firmadigital.servicio.token.TokenTimeout.DEFAULT_TIMEOUT;

import java.io.IOException;
import java.net.URL;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Base64;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.EJB;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;
import javax.validation.constraints.NotNull;
import ec.gob.firmadigital.servicio.model.Documento;
import ec.gob.firmadigital.servicio.token.ServicioToken;
import ec.gob.firmadigital.servicio.token.TokenExpiradoException;
import ec.gob.firmadigital.servicio.token.TokenInvalidoException;
import ec.gob.firmadigital.servicio.token.TokenTimeout;
import ec.gob.firmadigital.servicio.util.Base64InvalidoException;
import ec.gob.firmadigital.servicio.util.FileUtil;
import io.rubrica.exceptions.CertificadoInvalidoException;
import io.rubrica.exceptions.DocumentoException;
import io.rubrica.exceptions.InvalidFormatException;
import io.rubrica.sign.SignInfo;
import io.rubrica.sign.Signer;
import io.rubrica.sign.pdf.PDFSignerItext;
import io.rubrica.sign.xades.XAdESSigner;
import io.rubrica.utils.Utils;
import java.io.ByteArrayInputStream;
import java.io.InputStream;
import java.io.StringReader;
import javax.json.Json;
import javax.json.JsonObject;
import javax.json.JsonReader;

/**
 * Servicio para almacenar, actualizar y obtener documentos desde los sistemas
 * transversales y la aplicación en firmadigital-api
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
public class ServicioDocumento {

    @EJB
    private ServicioToken servicioToken;

    @EJB
    private ServicioSistemaTransversal servicioSistemaTransversal;

    @EJB
    private ServicioLog servicioLog;

    @PersistenceContext
    private EntityManager em;

    private static final Logger logger = Logger.getLogger(ServicioDocumento.class.getName());

    /**
     * Crea documentos en el sistema, para ser firmados por un cliente.
     *
     * @param cedula
     * @param nombreSistema
     * @param archivos
     * @return
     * @throws ec.gob.firmadigital.servicio.util.Base64InvalidoException
     */
    public String crearDocumentos(@NotNull String cedula, @NotNull String nombreSistema,
            @NotNull Map<String, String> archivos) throws Base64InvalidoException {

        // Verificar si existe el sistema
        servicioSistemaTransversal.buscarSistema(nombreSistema);

        List<String> ids = new ArrayList<>();

        for (String nombre : archivos.keySet()) {
            String archivo = archivos.get(nombre);

            // Crear nuevo documento
            Documento documento = new Documento();
            documento.setCedula(cedula);
            documento.setNombre(nombre);
            documento.setFecha(new Date());
            documento.setSistema(nombreSistema);
            documento.setArchivo(decodificarBase64(archivo));

            // Almacenar
            em.persist(documento);
            ;
//            String cargo = "";
            // Agregar a la lista de Ids
            ids.add(documento.getId().toString());
        }

        Map<String, Object> parametros = new HashMap<>();
        parametros.put("cedula", cedula);
        parametros.put("sistema", nombreSistema);
        parametros.put("ids", String.join(",", ids));

        // Expiracion del Token
        Date expiracion = TokenTimeout.addMinutes(new Date(), DEFAULT_TIMEOUT);

        // Retorna el Token
        return servicioToken.generarToken(parametros, expiracion);
    }

    /**
     * Obtiene un documento mediante un token.
     *
     * @param token
     * @return
     * @throws TokenInvalidoException
     * @throws TokenExpiradoException
     */
    public Map<Long, String> obtenerDocumentos(String token) throws TokenInvalidoException, TokenExpiradoException {
        Map<String, Object> parametros = servicioToken.parseToken(token);
        String ids = (String) parametros.get("ids");
        logger.fine("ids=" + ids);

        Map<Long, String> archivos = new HashMap<>();

        for (String id : convertirEnList(ids)) {
            Long primaryKey = Long.parseLong(id);
            Documento documento = em.find(Documento.class, primaryKey);
            String archivo = null;
            if (documento != null) {
                archivo = codificarBase64(documento.getArchivo());
                archivos.put(primaryKey, archivo);
            }
        }

        return archivos;
    }

    /**
     *
     * @param token
     * @param archivos
     * @param cedulaJson
     * @param base64
     * @return
     * @throws ec.gob.firmadigital.servicio.token.TokenInvalidoException
     * @throws ec.gob.firmadigital.servicio.CedulaInvalidaException
     * @throws ec.gob.firmadigital.servicio.token.TokenExpiradoException
     * @throws ec.gob.firmadigital.servicio.util.Base64InvalidoException
     * @throws ec.gob.firmadigital.servicio.CertificadoRevocadoException
     * @throws ec.gob.firmadigital.servicio.DocumentoNoExisteException
     */
    public int actualizarDocumentos(String token, Map<Long, String> archivos, String cedulaJson, String base64)
            throws TokenInvalidoException, CedulaInvalidaException, TokenExpiradoException, Base64InvalidoException,
            CertificadoRevocadoException, DocumentoNoExisteException {

        Map<String, Object> parametros = servicioToken.parseToken(token);

        String ids = (String) parametros.get("ids");
        logger.info("ids=" + ids);

        String cedulaToken = (String) parametros.get("cedula");
        logger.info("cedulaToken=" + FileUtil.hashMD5(cedulaToken));
        logger.info("cedulaJson=" + FileUtil.hashMD5(cedulaJson));

        if (!cedulaToken.equals(cedulaJson)) {
            throw new CedulaInvalidaException("La cedula " + cedulaJson + " es incorrecta");
        }

        String nombreSistema = (String) parametros.get("sistema");
        URL url = servicioSistemaTransversal.buscarUrlSistema(nombreSistema);
        logger.info("sistema=" + nombreSistema);

        List<String> idList = convertirEnList(ids);

        if (idList.size() != archivos.size()) {
            throw new IllegalArgumentException("El token contiene " + idList.size()
                    + " archivos por procesar pero se enviaron solo " + archivos.size() + " archivos!");
        }

        int documentosFirmados = 0;

        for (String id : idList) {
            Long primaryKey = Long.parseLong(id);
            String archivoBase64 = archivos.get(primaryKey);

            if (archivoBase64 == null) {
                throw new IllegalArgumentException(
                        "El token contiene una lista de archivos distinta a los archivos solicitados para actualizar: "
                        + ids);
            }

            // Actualizar el archivo
            Documento documento = em.find(Documento.class, primaryKey);

            if (documento == null) {
                logger.warning("El documento " + primaryKey + " no existe en la base de datos");
                throw new DocumentoNoExisteException("El documento " + primaryKey + " no existe en la base de datos");
            }

            byte[] byteDocumento = java.util.Base64.getDecoder().decode(archivoBase64);
            java.util.List<SignInfo> signInfos;

            // Obtener el nombre del firmante para almacenar el documento en el
            // sistema transversal
            String datosFirmante = "";
            try {
                io.rubrica.certificate.to.Documento documentoTo = null;
                try {
                    // Se valida la extension del archivo
                    String mimeTypeRest = FileUtil.getMimeType(byteDocumento);
                    if (mimeTypeRest.contains("pdf")) {
                        InputStream inputStreamDocumento = new ByteArrayInputStream(byteDocumento);
                        PdfReader pdfReader = new PdfReader(inputStreamDocumento);
                        Signer signer = new PDFSignerItext();
                        signInfos = signer.getSigners(byteDocumento);
                        documentoTo = Utils.pdfToDocumento(pdfReader, signInfos);
                        datosFirmante = documentoTo.getCertificados().get(documentoTo.getCertificados().size() - 1).getDatosUsuario().getNombre()
                                + documentoTo.getCertificados().get(documentoTo.getCertificados().size() - 1).getDatosUsuario().getApellido();
                    }
                    if (mimeTypeRest.contains("xml")) {
                        datosFirmante = "";
                        XAdESSigner xAdESSigner = new XAdESSigner();
                        documentoTo = Utils.signInfosToCertificados(xAdESSigner.getSigners(byteDocumento));
                    }
                } catch (InvalidFormatException | IOException e) {
                    throw new IllegalArgumentException("Error en la verificacion de firma", e);
                } catch (DocumentoException | CertificadoInvalidoException ex) {
                    Logger.getLogger(ServicioDocumento.class.getName()).log(Level.SEVERE, null, ex);
                } catch (Exception ex) {
                    Logger.getLogger(ServicioDocumento.class.getName()).log(Level.SEVERE, null, ex);
                }
                String apiKeyRest = servicioSistemaTransversal.buscarApiKeyRest(nombreSistema);
                if (apiKeyRest != null) {
                    servicioSistemaTransversal.almacenarDocumentoREST(documentoTo, documento.getCedula(), documento.getNombre(),
                            archivoBase64, url, apiKeyRest);
                } else {
                    servicioSistemaTransversal.almacenarDocumento(documento.getCedula(), documento.getNombre(),
                            archivoBase64, datosFirmante, url);
                }
                documentosFirmados++;

                logger.log(Level.INFO, "Documento enviado al sistema {0}, firmado por {1}, sistema operativo {2}, tamano documento (bytes) {3}", new Object[]{nombreSistema, FileUtil.hashMD5(cedulaToken), obtenerSO(base64), documento.getArchivo().length});
                servicioLog.info("ServicioDocumento::actualizarDocumentos",
                        "Documento enviado al sistema " + nombreSistema
                        + ", firmado por " + FileUtil.hashMD5(cedulaToken)
                        + ", sistema operativo " + obtenerSO(base64)
                        + ", tamano documento (bytes) " + documento.getArchivo().length);
            } catch (SistemaTransversalException e) {
                String mensajeError = "No se pudo enviar el documento al sistema " + nombreSistema;
                servicioLog.error("ServicioDocumento::actualizarDocumentos", mensajeError);
                logger.log(Level.SEVERE, mensajeError);
            }

            // Eliminar el documento
            em.remove(documento);
        }

        return documentosFirmados;
    }

    /**
     * Convierte una cadena de texto con una lista separada por comas de ints en
     * una List.
     *
     * @param ids
     * @return
     */
    private List<String> convertirEnList(String ids) {
        // Separar por "espacio en blanco, coma, espacio en blanco":
        return Arrays.asList(ids.split("\\s*,\\s*"));
    }

    private byte[] decodificarBase64(String base64) throws Base64InvalidoException {
        try {
            return Base64.getDecoder().decode(base64);
        } catch (IllegalArgumentException e) {
            throw new Base64InvalidoException(e);
        }
    }

    private String codificarBase64(byte[] data) {
        return Base64.getEncoder().encodeToString(data);
    }

    private String obtenerSO(String base64) {
        String toString = new String(Base64.getDecoder().decode(base64));
        JsonObject jsonObjectBase64;
        try (JsonReader jsonReader = Json.createReader(new StringReader(toString))) {
            jsonObjectBase64 = jsonReader.readObject();
        }
        return jsonObjectBase64.getString("sistemaOperativo");
    }
}
