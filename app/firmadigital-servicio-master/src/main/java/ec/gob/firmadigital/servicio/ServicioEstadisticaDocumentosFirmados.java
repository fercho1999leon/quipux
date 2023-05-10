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

import java.math.BigInteger;
import java.util.List;
import java.util.logging.Logger;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;
import javax.validation.constraints.NotNull;

import javax.ejb.EJB;
import javax.json.Json;
import javax.json.JsonArrayBuilder;
import javax.json.JsonObjectBuilder;

/**
 * Buscar en una lista de URLs permitidos para utilizar como API. Esto permite
 * federar la utilización de FirmaEC sobre otra infraestructura, consultando en
 * una lista de servidores permitidos.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>, Misael Fernández
 */
@Stateless
public class ServicioEstadisticaDocumentosFirmados {

    @EJB
    private ServicioLog servicioLog;

    @PersistenceContext
    private EntityManager em;

    private static final Logger logger = Logger.getLogger(ServicioEstadisticaDocumentosFirmados.class.getName());

    /**
     * Busca un ApiUrl por URL.
     *
     * @param sistema
     * @param fechaDesde
     * @param fechaHasta
     * @return
     * @throws ApiEstadisticaException
     */
    public String buscarPorFechaDesdeFechaHasta(@NotNull String sistema, @NotNull String fechaDesde, @NotNull String fechaHasta) throws ApiEstadisticaException {
        String retorno = "";
        try {
            String nativeQuery = "SELECT s.descripcion, \n"
                    + "	count(*) AS total_firmas, \n"
                    + "	count(DISTINCT SUBSTRING(l.descripcion, \n"
                    + "	POSITION('firmado por' IN l.descripcion) + length('firmado por'), \n"
                    + "	((CASE WHEN POSITION(', documento' IN l.descripcion) = 0 THEN\n"
                    + "		length(l.descripcion)\n"
                    + "	ELSE\n"
                    + "		POSITION(', documento' IN l.descripcion)\n"
                    + "	END) -\n"
                    + "	(POSITION('firmado por' IN l.descripcion) + length('firmado por'))))) AS total_usuarios, \n"
                    + "	EXTRACT (month FROM fecha) AS month,\n"
                    + "	EXTRACT (year FROM fecha) AS year\n"
                    + "FROM log l INNER JOIN sistema s ON l.descripcion like '%'||s.nombre||',%'\n"
                    + "WHERE\n"
                    + " s.nombre = '" + sistema + "' AND --nombre del sistema\n"
                    + "	severidad = 0 AND --documentos firmados con éxito\n"
                    + "	fecha BETWEEN '" + fechaDesde + "' AND '" + fechaHasta + "'\n"
                    + "GROUP BY EXTRACT (month FROM fecha), EXTRACT (year FROM fecha), s.descripcion";
            List<Object[]> query = em.createNativeQuery(nativeQuery).getResultList();
            // Para construir un array de firmantes
            JsonArrayBuilder arrayBuilder = Json.createArrayBuilder();
            for (Object[] result : query) {
                JsonObjectBuilder builder = Json.createObjectBuilder();
                builder.add("descripcion", (String) result[0]);
                builder.add("total_firmas", (BigInteger) result[1]);
                builder.add("total_usuarios", (BigInteger) result[2]);
                builder.add("month", (double) result[3]);
                builder.add("year", (double) result[4]);
                arrayBuilder.add(builder);
            }
            String json = arrayBuilder.build().toString();
            retorno = json;
            servicioLog.info("ServicioApiUrl::buscarPorFechaDesdeFechaHasta", "Estadística de documentos firmados con fecha desde: " + fechaDesde + " | fecha hasta: " + fechaHasta);
//        } catch (java.lang.Exception e) {
//            e.printStackTrace();
        } catch (java.lang.IndexOutOfBoundsException e) {
            retorno = "No se encuentran registros para generar estadísticas";
            logger.severe(retorno);
            servicioLog.error("ServicioApiUrl::buscarPorFechaDesdeFechaHasta", retorno);
            throw new ApiEstadisticaException(retorno);
        } catch (java.lang.ClassCastException e) {
            retorno = "Problema con tipo de dato en la base de datos";
            logger.severe(retorno);
            servicioLog.error("ServicioApiUrl::buscarPorFechaDesdeFechaHasta", retorno);
            throw new ApiEstadisticaException(retorno);
        } catch (javax.persistence.PersistenceException e) {
            retorno = "Problema con el formato fecha";
            logger.severe(retorno + ": " + "fecha desde: " + fechaDesde + " | fecha hasta: " + fechaHasta);
            servicioLog.error("ServicioApiUrl::buscarPorFechaDesdeFechaHasta", retorno + ": " + "fecha desde: " + fechaDesde + " | fecha hasta: " + fechaHasta);
            throw new ApiEstadisticaException(retorno);
        } finally {
            return retorno;
        }
    }
}
