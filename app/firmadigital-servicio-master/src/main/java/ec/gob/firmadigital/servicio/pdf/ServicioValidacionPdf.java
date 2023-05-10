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
package ec.gob.firmadigital.servicio.pdf;

import java.io.IOException;
import java.security.KeyStoreException;
import java.security.SignatureException;
import java.security.cert.X509Certificate;
import java.text.SimpleDateFormat;
import java.util.List;
import java.util.logging.Logger;

import javax.ejb.EJB;
import javax.ejb.Stateless;
import javax.json.Json;
import javax.json.JsonArray;
import javax.json.JsonArrayBuilder;
import javax.json.JsonObjectBuilder;
import javax.ws.rs.Consumes;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;

import ec.gob.firmadigital.servicio.CertificadoRevocadoException;
import ec.gob.firmadigital.servicio.crl.ServicioConsultaCrl;
import ec.gob.firmadigital.servicio.util.Base64InvalidoException;
import ec.gob.firmadigital.servicio.util.Base64Util;
import io.rubrica.certificate.CertEcUtils;
import io.rubrica.exceptions.OcspValidationException;
import io.rubrica.exceptions.InvalidFormatException;
import io.rubrica.sign.SignInfo;
import io.rubrica.sign.Signer;
import io.rubrica.certificate.to.DatosUsuario;
import io.rubrica.sign.pdf.PDFSignerItext;
import io.rubrica.utils.Utils;

/**
 * Servicio de verificacion de archivos PDF.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
@Path("/validacionpdf")
public class ServicioValidacionPdf {

    @EJB
    private ServicioConsultaCrl servicioCrl;

    private static final Logger LOGGER = Logger.getLogger(ServicioValidacionPdf.class.getName());

    public String getNombre(byte[] pdf) throws IOException, InvalidFormatException, CertificadoRevocadoException {
        Signer signer = new PDFSignerItext();
        List<SignInfo> singInfos = signer.getSigners(pdf);

        if (!singInfos.isEmpty()) {
            SignInfo firma = singInfos.get(0);
            X509Certificate certificado = firma.getCerts()[0];

            LOGGER.info("Verificando CRL local del certificado");
            boolean revocado = servicioCrl.isRevocado(certificado.getSerialNumber());
            LOGGER.info("revocado=" + revocado);

            if (revocado) {
                throw new CertificadoRevocadoException();
            }

            return Utils.getCN(certificado);
        } else {
            return "Unknown";
        }
    }

    @POST
    @Consumes(MediaType.TEXT_PLAIN)
    @Produces(MediaType.APPLICATION_JSON)
    public Response verificarPdf(String archivoBase64)
            throws KeyStoreException, SignatureException, OcspValidationException {

        byte[] pdf;

        try {
            pdf = Base64Util.decode(archivoBase64);
        } catch (Base64InvalidoException e) {
            return Response.status(Status.BAD_REQUEST).entity("Error al decodificar Base64").build();
        }

        Signer signer = new PDFSignerItext();
        List<SignInfo> firmas;

        try {
            firmas = signer.getSigners(pdf);
        } catch (InvalidFormatException | IOException e) {
            return Response.status(Status.BAD_REQUEST).entity("Error al verificar PDF: \"" + e.getMessage() + "\"")
                    .build();
        }

        // Para construir un array de firmantes
        JsonArrayBuilder arrayBuilder = Json.createArrayBuilder();
        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");

        for (SignInfo firma : firmas) {
            //arreglar certificados invalidos
            JsonObjectBuilder builder = Json.createObjectBuilder();
            X509Certificate certificado = firma.getCerts()[0];
            DatosUsuario datosUsuario = CertEcUtils.getDatosUsuarios(certificado);

            builder.add("fecha", sdf.format(firma.getSigningTime()));
            builder.add("cedula", datosUsuario.getCedula());
            builder.add("nombre", datosUsuario.getNombre() + " " + datosUsuario.getApellido());
            builder.add("cargo", datosUsuario.getCargo());
            builder.add("institucion", datosUsuario.getInstitucion());
            arrayBuilder.add(builder);
        }

//        List<Certificado> certificados = Utils.verificarDocumento(pdf);
//        certificados.forEach((certificado) -> {
//            String apellido = certificado.getDatosUsuario().getApellido();
//            if (certificado.getDatosUsuario().getApellido() == null) {
//                apellido = "";
//            }
//            String nombre = certificado.getDatosUsuario().getNombre();
//            if (certificado.getDatosUsuario().getNombre() == null) {
//                nombre = "";
//            }
//            String validarFirma = Utils.validarFirma(certificado.getValidFrom(), certificado.getValidTo(), certificado.getGenerated(), certificado.getRevocated());
//            if (certificado.getDocVerify() != null && !certificado.getDocVerify()) {
//                validarFirma = "Inválida";
//            }
//            
//            JsonObjectBuilder builder = Json.createObjectBuilder();
//            builder.add("fecha", sdf.format(certificado.getGenerated().getTime()));
//            builder.add("cedula", certificado.getDatosUsuario().getCedula());
//            builder.add("nombre", nombre+ " " + apellido);
//            builder.add("cargo", certificado.getDatosUsuario().getCargo());
//            builder.add("institucion", certificado.getDatosUsuario().getInstitucion());
//            arrayBuilder.add(builder);
//            
//            String[] dataCert = new String[6];
//            dataCert[2] = certificado.getDocReason();
//            dataCert[3] = certificado.getDatosUsuario().getEntidadCertificadora();
//            dataCert[5] = validarFirma;
//        });
        // Construir JSON
        JsonArray jsonArray = arrayBuilder.build();
        JsonObjectBuilder objectBuilder = Json.createObjectBuilder();
        String json = objectBuilder.add("firmantes", jsonArray).build().toString();

        return Response.ok(json, MediaType.APPLICATION_JSON).build();
    }
}
