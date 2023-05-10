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

import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import java.io.StringWriter;
import java.net.MalformedURLException;
import java.net.URL;
import java.security.MessageDigest;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.NoResultException;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;
import javax.xml.bind.DatatypeConverter;
import jakarta.xml.soap.MessageFactory;
import jakarta.xml.soap.Name;
import jakarta.xml.soap.SOAPBody;
import jakarta.xml.soap.SOAPBodyElement;
import jakarta.xml.soap.SOAPConnection;
import jakarta.xml.soap.SOAPConnectionFactory;
import jakarta.xml.soap.SOAPException;
import jakarta.xml.soap.SOAPFactory;
import jakarta.xml.soap.SOAPMessage;
import javax.xml.transform.OutputKeys;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;

import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import ec.gob.firmadigital.servicio.model.Sistema;
import io.rubrica.certificate.to.Certificado;
import java.text.DateFormat;
import java.util.Calendar;
import javax.ws.rs.client.Client;
import javax.ws.rs.client.ClientBuilder;
import javax.ws.rs.client.Entity;
import javax.ws.rs.client.WebTarget;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;

/**
 * Servicio para invocar Web Services de los sistemas transaccionales, utilizado
 * para almacenar el documento ya firmado.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
public class ServicioSistemaTransversal {

    private static final String API_KEY_HEADER_PARAMETER = "X-API-KEY";

    @PersistenceContext(unitName = "FirmaDigitalDS")
    private EntityManager em;

    private static final SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");

    private static final Logger logger = Logger.getLogger(ServicioSistemaTransversal.class.getName());

    /**
     * Buscar un sistema transversal.
     *
     * @param nombre
     * @return
     * @throws IllegalArgumentException
     */
    public Sistema buscarSistema(String nombre) throws IllegalArgumentException {
        try {
            TypedQuery<Sistema> q = em.createQuery("SELECT s FROM Sistema s WHERE s.nombre = :nombre", Sistema.class);
            q.setParameter("nombre", nombre);
            return q.getSingleResult();
        } catch (NoResultException e) {
            throw new IllegalArgumentException("No se encontro el sistema " + nombre);
        }
    }

    /**
     * Obtiene el URL del Web Service de un sistema transversal, para devolver
     * el documento firmado por el usuario.
     *
     * @param nombre nombre del sistema transversal
     * @return el URL del sistema traansversal
     * @throws IllegalArgumentException si no se encuentra ese nombre de sistema
     * transversal
     */
    public URL buscarUrlSistema(String nombre) throws IllegalArgumentException {
        try {
            Sistema sistema = buscarSistema(nombre);
            return new URL(sistema.getURL());
        } catch (MalformedURLException e) {
            throw new IllegalArgumentException("El URL no es correcto: " + e.getMessage());
        }
    }

    /**
     * Obtiene el ApiKey del Web Service de un sistema transversal, para
     * devolver el documento firmado por el usuario.
     *
     * @param nombre nombre del sistema transversal
     * @return el ApiKey del servicio REST
     */
    public String buscarApiKeyRest(String nombre) {
        Sistema sistema = buscarSistema(nombre);
        return sistema.getApiKeyRest();
    }

    /**
     * Almacena el documento firmado en el sistema tranversarl, mediante la
     * invocación de un Web Service (SOAP).
     *
     * @param documento
     * @param cedula
     * @param nombreDocumento
     * @param archivoBase64
     * @param url
     * @param apiKeyRest
     * @throws SistemaTransversalException
     */
    public void almacenarDocumentoREST(io.rubrica.certificate.to.Documento documento, String cedula, String nombreDocumento, String archivoBase64, URL url, String apiKeyRest
    ) throws SistemaTransversalException {
        JsonObject jsonDoc = new JsonObject();
        jsonDoc.addProperty("cedula", cedula);
        jsonDoc.addProperty("nombreDocumento", nombreDocumento);
        jsonDoc.addProperty("archivo", archivoBase64);
        try {
            if (documento != null) {
                if (documento.getError() == null) {
                    jsonDoc.addProperty("firmasValidas", documento.getSignValidate());
                    jsonDoc.addProperty("integridadDocumento", documento.getDocValidate());
                    jsonDoc.addProperty("error", "null");
                    JsonArray arrayCer = new JsonArray();
                    for (Certificado cert : documento.getCertificados()) {
                        JsonObject jsonCer = new JsonObject();
                        jsonCer.addProperty("emitidoPara", cert.getIssuedTo());
                        jsonCer.addProperty("emitidoPor", cert.getIssuedBy());
                        jsonCer.addProperty("validoDesde", calendarToString(cert.getValidFrom()));
                        jsonCer.addProperty("validoHasta", calendarToString(cert.getValidTo()));
                        jsonCer.addProperty("fechaFirma", calendarToString(cert.getGenerated()));
                        jsonCer.addProperty("fechaRevocado", cert.getRevocated() != null ? calendarToString(cert.getRevocated()) : "");
                        jsonCer.addProperty("certificadoVigente", cert.getValidated());
                        jsonCer.addProperty("clavesUso", cert.getKeyUsages());
                        jsonCer.addProperty("fechaSelloTiempo", cert.getDocTimeStamp() != null ? dateToString(cert.getDocTimeStamp()) : "");
                        jsonCer.addProperty("integridadFirma", cert.getSignVerify());
                        jsonCer.addProperty("razonFirma", cert.getDocReason() != null ? cert.getDocReason() : "");
                        jsonCer.addProperty("localizacion", cert.getDocLocation() != null ? cert.getDocLocation() : "");
                        jsonCer.addProperty("cedula", cert.getDatosUsuario().getCedula());
                        jsonCer.addProperty("nombre", cert.getDatosUsuario().getNombre());
                        jsonCer.addProperty("apellido", cert.getDatosUsuario().getApellido());
                        jsonCer.addProperty("institucion", cert.getDatosUsuario().getInstitucion());
                        jsonCer.addProperty("cargo", cert.getDatosUsuario().getCargo());
                        jsonCer.addProperty("entidadCertificadora", cert.getDatosUsuario().getEntidadCertificadora());
                        jsonCer.addProperty("serial", cert.getDatosUsuario().getSerial());
                        jsonCer.addProperty("selladoTiempo", cert.getDatosUsuario().getSelladoTiempo());
                        jsonCer.addProperty("certificadoDigitalValido", cert.getDatosUsuario().isCertificadoDigitalValido());
                        arrayCer.add(jsonCer);
                    }
                    jsonDoc.add("certificado", arrayCer);
                } else {
                    jsonDoc.addProperty("firmasValidas", false);
                    jsonDoc.addProperty("integridadDocumento", false);
                    jsonDoc.addProperty("error", documento.getError());
                }
            }
        } catch (Exception exception) {
            jsonDoc = new JsonObject();
            jsonDoc.addProperty("firmasValidas", false);
            jsonDoc.addProperty("integridadDocumento", false);
            jsonDoc.addProperty("error", "El archivo no pudo ser validado o no es un PDF");
        }
        //Consumo del Servicio Web en REST
        Client client = ClientBuilder.newClient();
        WebTarget target = client.target(url.toString());
        Response response = target.request().header(API_KEY_HEADER_PARAMETER, apiKeyRest).header("Content-Type", MediaType.APPLICATION_JSON).post(Entity.json(jsonDoc.toString()));
        String resultado = response.readEntity(String.class);
        System.out.println("response.getStatus(): " + response.getStatus() + " response.getStatusInfo(): " + response.getStatusInfo() + " response.readEntity(String.class): " + resultado);
        if ("OK".equals(resultado)) {
            return;
        } else if ("ERROR".equals(resultado)) {
            throw new SistemaTransversalException("Se devuelve error del sistema transversal: " + resultado);
        } else {
            throw new SistemaTransversalException("Resultado invalido del sistema transversal: " + resultado);
        }
    }

    private String calendarToString(Calendar calendar) {
        Date date = calendar.getTime();
        DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return dateFormat.format(date);
    }

    private String dateToString(Date date) {
        DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return dateFormat.format(date);
    }

    /**
     * Almacena el documento firmado en el sistema tranversarl, mediante la
     * invocación de un Web Service (SOAP).
     *
     * @param usuario
     * @param documento
     * @param archivo
     * @param datosFirmante
     * @param url
     * @throws SistemaTransversalException
     */
    public void almacenarDocumento(String usuario, String documento, String archivo, String datosFirmante, URL url) throws SistemaTransversalException {
        try {
            MessageFactory factory = MessageFactory.newInstance();
            SOAPMessage soapMessage = factory.createMessage();
            SOAPBody body = soapMessage.getSOAPBody();

            SOAPFactory soapFactory = SOAPFactory.newInstance();
            Name bodyName = soapFactory.createName("grabar_archivos_firmados", "urn", "urn:soapapiorfeo");
            SOAPBodyElement bodyElement = body.addBodyElement(bodyName);
            bodyElement.addChildElement("set_var_usuario").addTextNode(usuario);
            bodyElement.addChildElement("set_var_documento").addTextNode(documento);
            bodyElement.addChildElement("set_var_archivo").addTextNode(archivo);
            bodyElement.addChildElement("set_var_datos_firmante").addTextNode(datosFirmante);
            bodyElement.addChildElement("set_var_fecha").addTextNode(sdf.format(new Date()));
            String institucion = "";
            String cargo = "";
            bodyElement.addChildElement("set_var_institucion").addTextNode(institucion);
            bodyElement.addChildElement("set_var_cargo").addTextNode(cargo);

            SOAPConnection connection = SOAPConnectionFactory.newInstance().createConnection();
            SOAPMessage response = connection.call(soapMessage, url);
            connection.close();

            SOAPBody soapBody = response.getSOAPBody();

            NodeList nl = soapBody.getElementsByTagName("result");
            Node node = nl.item(0);

            if (node == null) {
                logger.severe("Error al invocar el Web Service: " + convertToString(soapBody));
                throw new SistemaTransversalException("Error al invocar el Web Service");
            }

            // 0 is error, 1 ok
            String resultado = node.getTextContent();
            logger.fine("Resultado enviado por el sistema transversal: " + resultado);

            if ("1".equals(resultado)) {
                return;
            } else if ("0".equals(resultado)) {
                throw new SistemaTransversalException("Se devuelve error del sistema transversal: " + resultado);
            } else {
                throw new SistemaTransversalException("Resultado invalido del sistema transversal: " + resultado);
            }
        } catch (SOAPException e) {
            String mensaje = (String) e.getMessage();
            //System.out.println("Exception Normal " + mensaje);
            if (mensaje != null) {
                if (mensaje.contains("SOAP message could not be sent")) {
                    System.out.println("Mensaje SOAP no pudo ser enviado");
                }
            } else {
                System.out.println("----------");
                logger.log(Level.SEVERE, "Error al actualizar el documento en el sistema transversal", e);
                System.out.println("----------");
            }
            throw new SistemaTransversalException("Error al invocar Web Service del sistema transversal", e);
        }
    }

    public boolean verificarApiKey(String nombre, String apiKey) {
        // Verificar si existe el Sistema
        Sistema sistema;

        try {
            sistema = buscarSistema(nombre);
        } catch (IllegalArgumentException e) {
            logger.severe("No existe el sistema: " + nombre);
            return false;
        }

        String apiKeySistema = sistema.getApiKey().toUpperCase();
        logger.fine("apiKeySistema=" + apiKey);

        // Si no tiene API Key dejar pasar la invocacion!
        if (apiKeySistema == null) {
            logger.warning("API KEY is null, sistema=" + nombre);
            return true;
        }

        String hash = hashSha256(apiKey).toUpperCase();
        return apiKeySistema.equals(hash);
    }

    private String hashSha256(String apiKey) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            md.update(apiKey.getBytes("UTF-8"));
            byte[] digest = md.digest();
            return DatatypeConverter.printHexBinary(digest).toLowerCase();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    private String convertToString(SOAPBody message) throws SistemaTransversalException {
        try {
            Document doc = message.extractContentAsDocument();
            StringWriter sw = new StringWriter();
            TransformerFactory tf = TransformerFactory.newInstance();
            Transformer transformer = tf.newTransformer();
            transformer.setOutputProperty(OutputKeys.OMIT_XML_DECLARATION, "no");
            transformer.setOutputProperty(OutputKeys.METHOD, "xml");
            transformer.setOutputProperty(OutputKeys.INDENT, "yes");
            transformer.setOutputProperty(OutputKeys.ENCODING, "UTF-8");
            transformer.transform(new DOMSource(doc), new StreamResult(sw));
            return sw.toString();
        } catch (Exception e) {
            throw new SistemaTransversalException(e.getMessage());
        }
    }
}
