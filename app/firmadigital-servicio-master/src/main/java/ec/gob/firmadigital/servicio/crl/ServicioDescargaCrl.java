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
package ec.gob.firmadigital.servicio.crl;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.math.BigInteger;
import java.security.cert.CRLException;
import java.security.cert.CertificateException;
import java.security.cert.CertificateFactory;
import java.security.cert.X509CRL;
import java.security.cert.X509CRLEntry;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.sql.Statement;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.annotation.PostConstruct;
import javax.annotation.Resource;
import javax.ejb.EJBException;
import javax.ejb.Schedule;
import javax.ejb.Singleton;
import javax.ejb.Startup;
import javax.sql.DataSource;
import io.rubrica.crl.ServicioCRL;
import io.rubrica.utils.HttpClient;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Servicio para cargar los CRLs de las CAs soportadas en una tabla.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Singleton
//GRANJA DE SERVIDORES EN PRODUCCION - COMENTAR EVITAR DESCARGA CRL
@Startup
//GRANJA DE SERVIDORES EN PRODUCCION - COMENTAR EVITAR DESCARGA CRL
public class ServicioDescargaCrl {

    @Resource(lookup = "java:/FirmaDigitalDS")
    private DataSource ds;

    private static final Logger logger = Logger.getLogger(ServicioDescargaCrl.class.getName());

    //GRANJA DE SERVIDORES EN PRODUCCION - COMENTAR EVITAR DESCARGA CRL
    @PostConstruct
    public void init() {
        crearTablaSiNoExiste();
        importarCrls();
    }

    //10 segundos
    //@Schedule(hour = "*", minute = "*", second = "*/10", persistent = false)
    //5 minutos
    @Schedule(hour = "*", minute = "*/5", persistent = false)
    //1 hora
    //@Schedule(minute = "0", hour = "*", persistent = false)
    //GRANJA DE SERVIDORES EN PRODUCCION - COMENTAR EVITAR DESCARGA CRL

    public void importarCrls() {
        logger.info("Iniciando el proceso de descarga de CRL");

        logger.info("Descargando CRL de BCE...");
        X509CRL bceCrl = downloadCrl(ServicioCRL.BCE_CRL);

        logger.info("Descargando CRL de Security Data 1...");
        X509CRL sdCrl1 = downloadCrl(ServicioCRL.SD_CRL1);

        logger.info("Descargando CRL de Security Data 2...");
        X509CRL sdCrl2 = downloadCrl(ServicioCRL.SD_CRL2);

        logger.info("Descargando CRL de Security Data 3...");
        X509CRL sdCrl3 = downloadCrl(ServicioCRL.SD_CRL3);

        logger.info("Descargando CRL de Security Data 4...");
        X509CRL sdCrl4 = downloadCrl(ServicioCRL.SD_CRL4);

        logger.info("Descargando CRL de Security Data 5...");
        X509CRL sdCrl5 = downloadCrl(ServicioCRL.SD_CRL5);

        logger.info("Descargando CRL de CJ...");
        X509CRL cjCrl = downloadCrl(ServicioCRL.CJ_CRL);

        logger.info("Descargando CRL de ANFAC...");
        X509CRL anfAcCrl = downloadCrl(ServicioCRL.ANFAC_CRL);

        logger.info("Descargando CRL de DIGERCIC...");
        X509CRL digercicCrl = downloadCrl(ServicioCRL.DIGERCIC_CRL);

        logger.info("Descargando CRL de UANATACA1...");
        X509CRL uanatacaCrl1 = downloadCrl(ServicioCRL.UANATACA_CRL1);

        logger.info("Descargando CRL de UANATACA2...");
        X509CRL uanatacaCrl2 = downloadCrl(ServicioCRL.UANATACA_CRL2);

        logger.info("Descargando CRL de DATIL...");
        X509CRL datilCrl = downloadCrl(ServicioCRL.DATIL_CRL);

        logger.info("Descargando CRL de ARGOSDATA...");
        X509CRL argosDataCrl = downloadCrl(ServicioCRL.ARGOSDATA_CRL);

        logger.info("Descargando CRL de LAZZATE...");
        X509CRL lazzateCrl = downloadCrl(ServicioCRL.LAZZATE_CRL);

        try (Connection conn = ds.getConnection();
                PreparedStatement ps = conn.prepareStatement(
                        "INSERT INTO crl (serial, fecharevocacion, razonrevocacion, entidadcertificadora) VALUES (?,?,?,?) "
                        + "ON CONFLICT (serial) "
                        + "DO UPDATE SET fecharevocacion = EXCLUDED.fecharevocacion, razonrevocacion = EXCLUDED.razonrevocacion, entidadcertificadora = EXCLUDED.entidadcertificadora")) {

            logger.info("Iniciando actualizacion de CRLs...");

            int contadorBCE = 0;
            int contadorSD1 = 0, contadorSD2 = 0, contadorSD3 = 0, contadorSD4 = 0, contadorSD5 = 0;
            int contadorCJ = 0;
            int contadorANFAC = 0;
            int contadorDIGERCIC = 0;
            int contadorUANATACA1 = 0, contadorUANATACA2 = 0;
            int contadorDATIL = 0;
            int contadorARGOSDATA = 0;
            int contadorLAZZATE = 0;

            if (bceCrl != null) {
                contadorBCE = insertarCrl(bceCrl, 1, ps);
                logger.info("Registros insertados/actualizados BCE: " + contadorBCE);
            } else {
                logger.info("No se inserta BCE");
            }

            if (sdCrl1 != null) {
                contadorSD1 = insertarCrl(sdCrl1, 2, ps);
                logger.info("Registros insertados/actualizados Security Data 1: " + contadorSD1);
            } else {
                logger.info("No se inserta Security Data 1");
            }

            if (sdCrl2 != null) {
                contadorSD2 = insertarCrl(sdCrl2, 2, ps);
                logger.info("Registros insertados/actualizados Security Data 2: " + contadorSD2);
            } else {
                logger.info("No se inserta Security Data 2");
            }

            if (sdCrl3 != null) {
                contadorSD3 = insertarCrl(sdCrl3, 2, ps);
                logger.info("Registros insertados/actualizados Security Data 3: " + contadorSD3);
            } else {
                logger.info("No se inserta Security Data 3");
            }

            if (sdCrl4 != null) {
                contadorSD4 = insertarCrl(sdCrl4, 2, ps);
                logger.info("Registros insertados/actualizados Security Data 4: " + contadorSD4);
            } else {
                logger.info("No se inserta Security Data 4");
            }

            if (sdCrl5 != null) {
                contadorSD5 = insertarCrl(sdCrl5, 2, ps);
                logger.info("Registros insertados/actualizados Security Data 5: " + contadorSD5);
            } else {
                logger.info("No se inserta Security Data 5");
            }

            if (cjCrl != null) {
                contadorCJ = insertarCrl(cjCrl, 3, ps);
                logger.info("Registros insertados/actualizados CJ: " + contadorCJ);
            } else {
                logger.info("No se inserta CJ");
            }

            if (anfAcCrl != null) {
                contadorANFAC = insertarCrl(anfAcCrl, 4, ps);
                logger.info("Registros insertados/actualizados ANFAC: " + contadorANFAC);
            } else {
                logger.info("No se inserta ANFAC");
            }

            if (uanatacaCrl1 != null) {
                contadorUANATACA1 = insertarCrl(uanatacaCrl1, 5, ps);
                logger.info("Registros insertados/actualizados UANATACA 1: " + contadorUANATACA1);
            } else {
                logger.info("No se inserta UANATACA 1");
            }

            if (uanatacaCrl2 != null) {
                contadorUANATACA2 = insertarCrl(uanatacaCrl2, 6, ps);
                logger.info("Registros insertados/actualizados UANATACA 2: " + contadorUANATACA2);
            } else {
                logger.info("No se inserta UANATACA 2");
            }

            if (digercicCrl != null) {
                contadorDIGERCIC = insertarCrl(digercicCrl, 7, ps);
                logger.info("Registros insertados/actualizados DIGERCIC: " + contadorDIGERCIC);
            } else {
                logger.info("No se inserta DIGERCIC");
            }

            if (datilCrl != null) {
                contadorDATIL = insertarCrl(datilCrl, 8, ps);
                logger.info("Registros insertados/actualizados DATIL: " + contadorDATIL);
            } else {
                logger.info("No se inserta DATIL");
            }

            if (argosDataCrl != null) {
                contadorARGOSDATA = insertarCrl(argosDataCrl, 9, ps);
                logger.info("Registros insertados/actualizados ARGOSDATA: " + contadorARGOSDATA);
            } else {
                logger.info("No se inserta ARGOSDATA");
            }

            if (lazzateCrl != null) {
                contadorLAZZATE = insertarCrl(lazzateCrl, 10, ps);
                logger.info("Registros insertados/actualizados ARGOSDATA: " + contadorLAZZATE);
            } else {
                logger.info("No se inserta LAZZATE");
            }

            int total = contadorBCE + contadorSD1 + contadorSD2 + contadorSD3 + contadorSD4 + contadorSD5 + contadorCJ + contadorANFAC + contadorUANATACA1 + contadorUANATACA2 + contadorDIGERCIC + contadorDATIL + contadorARGOSDATA + contadorLAZZATE;
            logger.info("Registros insertados/actualizados Total: " + total);

            logger.info("Finalizado!");
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Error al insertar/actualizar certificados revocados", e);
            throw new EJBException(e);
        }
    }
    
    private int insertarCrl(X509CRL crl, int entidadCertificadora, PreparedStatement ps) throws SQLException {
        // Existen CRLs?
        if (crl.getRevokedCertificates() == null) {
            return 0;
        }

        for (X509CRLEntry cert : crl.getRevokedCertificates()) {
            BigInteger serial = cert.getSerialNumber();
            Date fechaRevocacion = cert.getRevocationDate();
            String razonRevocacion = cert.getRevocationReason() == null ? "" : cert.getRevocationReason().toString();
            LocalDateTime ldt = LocalDateTime.ofInstant(fechaRevocacion.toInstant(), ZoneId.systemDefault());

            //https://www.ipa.go.jp/security/rfc/RFC3280-04EN.html#41202
            Pattern pattern = Pattern.compile("\\d{1,2000}");
            Matcher matcher = pattern.matcher(serial.toString());
            if (matcher.matches()) {
                ps.setString(1, serial.toString());
                ps.setObject(2, ldt);
                ps.setString(3, razonRevocacion);
                ps.setInt(4, entidadCertificadora);
                ps.addBatch();
            } else {
                logger.log(Level.SEVERE, "Error con el serial number {0} de la entidad certificadora {1}", new Object[]{serial.toString(), entidadCertificadora});
            }
        }

        int[] count = ps.executeBatch();
        return count.length;
    }

    private X509CRL downloadCrl(String url) {
        byte[] content;

        try {
            HttpClient http = new HttpClient();
            content = http.download(url);
        } catch (IOException e) {
            logger.log(Level.SEVERE, "Error al descargar CRL de " + url, e);
            return null;
        }

        try {
            CertificateFactory cf = CertificateFactory.getInstance("X.509");
            return (X509CRL) cf.generateCRL(new ByteArrayInputStream(content));
        } catch (CertificateException | CRLException e) {
            logger.log(Level.SEVERE, "Error al descargar CRL de " + url + ": " + e.getMessage());
            return null;
        }
    }

    private void crearTablaSiNoExiste() {
        logger.info("Creando tabla CRL si es que no existe...");

        try (Connection conn = ds.getConnection(); Statement st = conn.createStatement()) {
            st.executeUpdate("CREATE TABLE IF NOT EXISTS crl ("
                    + "serial varchar(2000) NOT NULL, "
                    + "fecharevocacion varchar(2000) NULL, "
                    + "razonrevocacion varchar(2000) NULL, "
                    + "entidadcertificadora varchar(2000) NULL,	"
                    + "CONSTRAINT pk_serial PRIMARY KEY (serial))");
            logger.info("Tabla CRL creada");
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Error al crear tabla CRL", e);
            throw new EJBException(e);
        }
    }
}