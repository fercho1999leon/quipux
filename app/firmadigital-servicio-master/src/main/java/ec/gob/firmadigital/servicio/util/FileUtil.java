package ec.gob.firmadigital.servicio.util;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.security.MessageDigest;
import javax.xml.bind.DatatypeConverter;

import org.apache.tika.Tika;

/**
 *
 * @author bolivar.murillo msp
 */
public class FileUtil {

    public static String getMimeType(byte[] data) {

        Tika tika = new Tika();

        String mimeType = "";

        try (InputStream is = new ByteArrayInputStream(data)) {
            mimeType = tika.detect(is);
            mimeType = mimeType == null ? "" : mimeType;
        } catch (IOException e) {
            mimeType = "";
        }
        return mimeType;
    }

    public static String hashMD5(String texto) {
        try {
            MessageDigest md = MessageDigest.getInstance("MD5");
            md.update(texto.getBytes("UTF-8"));
            byte[] digest = md.digest();
            return DatatypeConverter.printHexBinary(digest).toLowerCase();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}
