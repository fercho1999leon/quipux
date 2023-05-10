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
package ec.gob.firmadigital.servicio.crl;

import java.math.BigInteger;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.annotation.Resource;
import javax.ejb.EJBException;
import javax.ejb.Stateless;
import javax.sql.DataSource;

/**
 * Servicio para consultar los CRLs.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
public class ServicioConsultaCrl {

    @Resource(lookup = "java:/FirmaDigitalDS")
    private DataSource ds;

    private static final Logger logger = Logger.getLogger(ServicioConsultaCrl.class.getName());

    public boolean isRevocado(BigInteger serial) {
        try (Connection conn = ds.getConnection();
                PreparedStatement ps = conn.prepareStatement("SELECT serial FROM crl WHERE serial=?")) {

            ps.setString(1, serial.toString());

            try (ResultSet rs = ps.executeQuery()) {
                return rs.next();
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Error al buscar certificado", e);
            throw new EJBException(e);
        }
    }

    public String fechaRevocado(BigInteger serial) {
        try (Connection conn = ds.getConnection();
                PreparedStatement ps = conn.prepareStatement("SELECT fecharevocacion FROM crl WHERE serial=?")) {

            ps.setString(1, serial.toString());

            try (ResultSet rs = ps.executeQuery()) {
                String fecharevocacion = null;

                while (rs.next()) {
                    fecharevocacion = rs.getString("fecharevocacion");
                }

                return fecharevocacion;
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Error al buscar certificado", e);
            throw new EJBException(e);
        }
    }
}
