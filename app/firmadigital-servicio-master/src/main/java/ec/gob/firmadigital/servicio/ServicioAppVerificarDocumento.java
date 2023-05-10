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

import com.itextpdf.kernel.pdf.PdfReader;
import javax.ejb.Stateless;

import io.rubrica.certificate.to.Documento;
import io.rubrica.exceptions.SignatureVerificationException;
import io.rubrica.sign.SignInfo;
import io.rubrica.sign.Signer;
import io.rubrica.sign.pdf.PDFSignerItext;
import io.rubrica.utils.Json;
import static io.rubrica.utils.Utils.pdfToDocumento;
import java.io.ByteArrayInputStream;
import java.io.InputStream;
import java.util.ArrayList;
import javax.validation.constraints.NotNull;

/**
 *
 * @author Christian Espinosa <christian.espinosa@mintel.gob.ec>, Misael
 * Fernández
 */
@Stateless
public class ServicioAppVerificarDocumento {

    public String verificarDocumento(@NotNull String base64Documento, @NotNull String base64) {
        String retorno = null;
        Documento documento = null;
        try {
            byte[] byteDocumento = java.util.Base64.getDecoder().decode(base64Documento);
            InputStream inputStreamDocumento = new ByteArrayInputStream(byteDocumento);
            PdfReader pdfReader = new PdfReader(inputStreamDocumento);
            Signer signer = new PDFSignerItext();
            java.util.List<SignInfo> signInfos;
            signInfos = signer.getSigners(byteDocumento);
            documento = pdfToDocumento(pdfReader, signInfos);
        } catch (java.lang.UnsupportedOperationException uoe) {
            retorno = "No es posible procesar el documento desde dispositivo móvil\nIntentar en FirmaEC de Escritorio";
        } catch (com.itextpdf.io.IOException ioe) {
            retorno = "El archivo no es PDF";
        } catch (SignatureVerificationException sve) {
            retorno = sve.toString();
        } catch (Exception ex) {
            retorno = ex.toString();
        }

        if (documento == null) {
            documento = new Documento(false, false, new ArrayList<>(), retorno);
        }

        return Json.generarJsonDocumento(documento);
    }

}
