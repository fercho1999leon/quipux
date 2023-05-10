/*
 * Copyright (C) 2020
 * Authors: Ricardo Arguello, Misael Fernández, Efraín Rodríguez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.*
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
package ec.gob.firmadigital.servicio.rest;

import com.google.gson.Gson;

import com.google.gson.JsonArray;
import io.rubrica.certificate.to.Certificado;
import io.rubrica.certificate.to.Documento;
import io.rubrica.utils.Utils;
import com.google.gson.JsonObject;
import com.itextpdf.kernel.pdf.PdfReader;
import io.rubrica.sign.SignInfo;
import io.rubrica.sign.Signer;
import io.rubrica.sign.pdf.PDFSignerItext;
import java.io.ByteArrayInputStream;

import javax.ejb.Stateless;
import javax.ws.rs.Consumes;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;
import java.io.InputStream;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

@Stateless
@Path("/validacionavanzadapdf")
public class ServicioValidacionPdfRest {

    @POST
    @Consumes(MediaType.TEXT_PLAIN)
    @Produces(MediaType.APPLICATION_JSON)
    public Response verificarPdf(String archivoBase64)
            throws Exception {
        byte[] byteDocumento = java.util.Base64.getDecoder().decode(archivoBase64);
        InputStream inputStreamDocumento = new ByteArrayInputStream(byteDocumento);
        PdfReader pdfReader = new PdfReader(inputStreamDocumento);
        Signer signer = new PDFSignerItext();
        java.util.List<SignInfo> signInfos;
        signInfos = signer.getSigners(byteDocumento);

        try {
            Documento documento = Utils.pdfToDocumento(pdfReader, signInfos);
            Gson gson = new Gson();
            JsonObject jsonDoc = new JsonObject();

            if (documento.getError() == null) {
                jsonDoc.addProperty("firmasValidas", documento.getSignValidate());
                jsonDoc.addProperty("integridadDocumento", documento.getDocValidate());
                jsonDoc.addProperty("integridadDocumento", documento.getDocValidate());
                jsonDoc.addProperty("error", "null");
                JsonArray arrayCer = new JsonArray();
                for (Certificado cert : documento.getCertificados()) {
//					String fecha = servicioCrl.fechaRevocado(new BigInteger(cert.getDatosUsuario().getSerial()));
//					Date fechaRevocado = UtilsCrlOcsp.fechaString_Date(fecha);
//					cert.setRevocated(Utils.dateToCalendar(fechaRevocado));

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
                String json = gson.toJson(jsonDoc);

                return Response.ok(json, MediaType.APPLICATION_JSON).build();

            } else {
                jsonDoc.addProperty("firmasValidas", false);
                jsonDoc.addProperty("integridadDocumento", false);
                jsonDoc.addProperty("error", documento.getError());
                String json = gson.toJson(jsonDoc);

                return Response.ok(json, MediaType.APPLICATION_JSON).build();
            }
        } catch (Exception exception) {
            Gson gson = new Gson();
            JsonObject jsonDoc = new JsonObject();
            jsonDoc.addProperty("firmasValidas", false);
            jsonDoc.addProperty("integridadDocumento", false);
            jsonDoc.addProperty("error", "El archivo no pudo ser validado o no es un PDF");
            String json = gson.toJson(jsonDoc);

            return Response.status(Status.BAD_REQUEST).entity(json).build();
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

}
