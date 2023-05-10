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
package ec.gob.firmadigital.servicio;

import com.itextpdf.kernel.crypto.BadPasswordException;
import ec.gob.firmadigital.servicio.util.FirmaDigital;
import ec.gob.firmadigital.servicio.util.JsonProcessor;
import ec.gob.firmadigital.servicio.util.Pkcs12;
import ec.gob.firmadigital.servicio.util.Propiedades;
import io.rubrica.exceptions.CertificadoInvalidoException;
import io.rubrica.exceptions.ConexionException;
import io.rubrica.exceptions.EntidadCertificadoraNoValidaException;
import io.rubrica.exceptions.HoraServidorException;
import io.rubrica.exceptions.RubricaException;
import io.rubrica.utils.X509CertificateUtils;
import java.io.IOException;
import java.net.HttpURLConnection;
import java.security.InvalidKeyException;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.security.NoSuchAlgorithmException;
import java.security.UnrecoverableKeyException;
import java.util.HashMap;
import java.util.Map;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.ejb.Stateless;
import javax.ws.rs.BadRequestException;
import javax.ws.rs.WebApplicationException;
import javax.ws.rs.client.Client;
import javax.ws.rs.client.ClientBuilder;
import javax.ws.rs.client.Entity;
import javax.ws.rs.client.Invocation;
import javax.ws.rs.client.WebTarget;
import javax.ws.rs.core.Form;
import javax.ws.rs.core.Response;

/**
 * Buscar en una lista de URLs permitidos para utilizar como API. Esto permite
 * federar la utilización de FirmaEC sobre otra infraestructura, consultando en
 * una lista de servidores permitidos.
 *
 * @author Christian Espinosa <christian.espinosa@mintel.gob.ec>, Misael
 * Fernández
 */
@Stateless
public class ServicioAppFirmarDocumentoTransversal {

    private final String REST_SERVICE_URL_PREPRODUCCION = "https://impapi.firmadigital.gob.ec/api";
//    private final String REST_SERVICE_URL_PREPRODUCCION = "http://impapi.firmadigital.gob.ec:8080/api";
//    private final String REST_SERVICE_URL_DESARROLLO = "http://impapi.firmadigital.gob.ec:8080/api";
    private final String REST_SERVICE_URL_DESARROLLO = "http://impapi.firmadigital.gob.ec:8181/api";
//    private final String REST_SERVICE_URL_DESARROLLO = "http://localhost:8080/api";
    private final String REST_SERVICE_URL_PRODUCCION = "https://api.firmadigital.gob.ec/api";
    private String restServiceUrl;
    private static final Logger logger = Logger.getLogger(ServicioAppFirmarDocumentoTransversal.class.getName());

    private String resultado = null;
    private String sistema = null;
    private String versionFirmaEC = null;
    private String formatoDocumento = null;
    private String llx = null;
    private String lly = null;
    private String tipoEstampado = null;
    private String razon = null;
    private String pagina = null;
    private boolean pre = false;
    private boolean des = false;
    private String base64 = null;
    private String url = null;

    private String cedula;

    public String firmarTransversal(String pkcs12, String password, String sistema,
            String operacion, String url, String versionFirmaEC, String formatoDocumento,
            String tokenJwt, String llx, String lly, String pagina, String tipoEstampado,
            String razon, boolean pre, boolean des, String base64) throws Exception {
        // Parametros opcionales
        this.sistema = sistema;
        this.versionFirmaEC = versionFirmaEC;
        this.formatoDocumento = formatoDocumento;
        this.llx = llx;
        this.lly = lly;
        this.tipoEstampado = tipoEstampado;
        this.razon = razon;
        this.pagina = pagina;
        this.url = url;
        this.pre = pre;
        this.des = des;
        this.base64 = base64;
        ambiente();
        //en caso de ser firma descentralizada
        if (url != null) {
            restServiceUrl = url;
        }
        Map<Long, byte[]> documentosFirmados;
        try {
            //bajar documentos a firmar
            String json = bajarDocumentos(tokenJwt);
            if (json != null) {
                //firmando documentos descargados
                documentosFirmados = firmarDocumentos(json, pkcs12, password);
                // Actualizar documentos
                actualizarDocumentos(tokenJwt, documentosFirmados, cedula);
            }
        } finally {
            return resultado;
        }
    }

    private void ambiente() {
        // Invocar el servicio de Preproduccio o Produccion?
        if (pre) {
            restServiceUrl = REST_SERVICE_URL_PREPRODUCCION;
        } else if (des) {
            restServiceUrl = REST_SERVICE_URL_DESARROLLO;
        } else {
            restServiceUrl = REST_SERVICE_URL_PRODUCCION;
        }
    }

    private Map<Long, byte[]> firmarDocumentos(String json, String pkcs12, String password)
            throws Exception {
        Map<Long, byte[]> documentos = JsonProcessor.parseJsonDocumentos(json);
        Map<Long, byte[]> documentosFirmados = new HashMap<>();
        String fechaHora = JsonProcessor.parseJsonFechaHora(json);
        // Firmar!
        for (Long id : documentos.keySet()) {
            byte[] documento = documentos.get(id);
            byte[] documentoFirmado = null;
            FirmaDigital firmador = new FirmaDigital();
            try {
                // Obtener keyStore
                KeyStore keyStore = Pkcs12.getKeyStore(pkcs12, password);
                String alias = Pkcs12.getAlias(keyStore);

                // Cedula de identidad contenida en el certificado:
                cedula = X509CertificateUtils.getCedula(keyStore, alias);

                if ("xml".equalsIgnoreCase(formatoDocumento)) {
                    documentoFirmado = firmador.firmarXML(keyStore, alias, documento, password.toCharArray(), null, url, base64);
                }
                if ("pdf".equalsIgnoreCase(formatoDocumento)) {
                    Properties properties = Propiedades.propiedades(versionFirmaEC, llx, lly, pagina, tipoEstampado, razon, null, fechaHora, base64);
                    documentoFirmado = firmador.firmarPDF(keyStore, alias, documento, password.toCharArray(), properties, url, base64);
                }
            } catch (ConexionException ce) {
                resultado = "Servidor FirmaEC: " + ce.getMessage();
                throw ce;
            } catch (BadPasswordException bpe) {
                resultado = "Documento protegido con contraseña";
                throw bpe;
            } catch (InvalidKeyException ie) {
                resultado = "Problemas al abrir el documento";
                throw ie;
            } catch (EntidadCertificadoraNoValidaException | CertificadoInvalidoException ecnve) {
                resultado = "Certificado no válido";
                throw ecnve;
            } catch (HoraServidorException hse) {
                resultado = "Problemas en la red\nIntente nuevamente o verifique su conexión";
                throw hse;
            } catch (UnrecoverableKeyException uke) {
                resultado = "Certificado Corrupto";
                throw uke;
            } catch (KeyStoreException kse) {
                resultado = "La contraseña es inválida";
                throw kse;
            } catch (RubricaException re) {
                resultado = "No es posible procesar el documento";
                throw re;
            } catch (IOException | NoSuchAlgorithmException e) {
                resultado = "Excepción no conocida: " + e.getMessage();
                System.out.println("resultado: " + resultado);
            }
            documentosFirmados.put(id, documentoFirmado);
        }
        return documentosFirmados;
    }

    private String bajarDocumentos(String tokenJwt) throws Exception {
        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(restServiceUrl + "/firmadigital/" + tokenJwt);
        Invocation.Builder builder = target.request();
        Invocation invocation = builder.buildGet();
        Response response = invocation.invoke();
        // Leer la respuesta
        int statusCode = response.getStatus();
        String body = null;
        body = response.readEntity(String.class);
        resultado = leerBodyErrores(statusCode, body);
        return body;
    }

    private void actualizarDocumentos(String tokenJwt, Map<Long, byte[]> documentosFirmados, String cedula)
            throws Exception {
        String json = JsonProcessor.buildJson(documentosFirmados, cedula);

        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(restServiceUrl + "/firmadigital/" + tokenJwt);
        Invocation.Builder builder = target.request();
        Form form = new Form();
        form.param("json", json);
        form.param("base64", base64);
        Invocation invocation = builder.buildPut(Entity.form(form));
        String body = null;
        try {
            Response response = invocation.invoke();
            // Leer la respuesta
            int statusCode = response.getStatus();
            body = response.readEntity(String.class);
            resultado = leerBodyErrores(statusCode, body);
            if (resultado.isEmpty()) {
                resultado = JsonProcessor.parseJsonDocumentoFirmado(body);
            }
        } catch (BadRequestException e) {
            logger.log(Level.SEVERE, "BadRequestException: " + e.getResponse().readEntity(String.class));
        } catch (WebApplicationException e) {
            logger.log(Level.SEVERE, "WebApplicationException: " + e.getResponse().readEntity(String.class));
        }
    }

    private String leerBodyErrores(int statusCode, String body) {
        String error = "";
        if (statusCode != HttpURLConnection.HTTP_OK) {
            if (body.contains("Token expirado")) {
                error = "El tiempo de vida del documento en el servidor, se encuentra expirado";
            }
            if (body.contains("Token gestionado")) {
                error = "El/Los documento(s) fueron gestionados";
            }
            if (body.contains("Token invalido")
                    || body.contains("No se encuentran documentos")
                    || body.contains("Error al invocar servicio de obtencion de documentos")
                    || body.contains("Base 64 inválido")) {
                error = "No se encontraron documentos para firmar.";
            }
            if (body.contains("Cedula invalida")) {
                error = "Certificado no corresponde al usuario.\nVuelva a intentarlo.";
            }
            if (body.contains("Certificado revocado")) {
                error = "Certificado puede estar caducado o revocado.\nVuelva a intentarlo.";
            }
            if (body.contains("Request Entity Too Large")) {
                error = "Problemas con los servicios web.\nComuníquese con el administrador de su sistema.";
            }
        }
        return error;
    }
}
