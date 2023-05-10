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

import java.util.logging.Logger;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.NoResultException;
import javax.persistence.NonUniqueResultException;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;
import javax.validation.constraints.NotNull;

import ec.gob.firmadigital.servicio.model.ApiUrl;
import javax.ejb.EJB;

/**
 * Buscar en una lista de URLs permitidos para utilizar como API. Esto permite
 * federar la utilización de FirmaEC sobre otra infraestructura, consultando en
 * una lista de servidores permitidos.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Stateless
public class ServicioApiUrl {

    @EJB
    private ServicioLog servicioLog;

    @PersistenceContext
    private EntityManager em;

    private static final Logger logger = Logger.getLogger(ServicioApiUrl.class.getName());

    /**
     * Busca un ApiUrl por URL.
     *
     * @param sistema
     * @param url
     * @return
     * @throws ApiUrlNoEncontradoException
     */
    public String buscarPorUrl(@NotNull String sistema, @NotNull String url) throws ApiUrlNoEncontradoException {
        String retorno = "";
        try {
            TypedQuery<ApiUrl> query = em.createNamedQuery("ApiUrl.findByUrl", ApiUrl.class);
            query.setParameter("url", url);
            ApiUrl apiUrl = query.getSingleResult();
            if (apiUrl.getStatus()) {
                retorno = "Url habilitada";
                servicioLog.info("ServicioApiUrl::buscarPorUrl",
                        "Sistema " + sistema
                        + ", URL consultada: " + url + ", " + retorno);
            } else {
                retorno = "Url deshabilitada";
                servicioLog.warning("ServicioApiUrl::buscarPorUrl",
                        "Sistema " + sistema
                        + ", URL consultada: " + url + ", " + retorno);
            }
        } catch (NoResultException e) {
            retorno = "URL no encontrado";
            logger.severe(retorno + ": " + url);
            servicioLog.error("ServicioApiUrl::buscarPorUrl",
                    "Sistema " + sistema
                    + ", URL consultada: " + url + ", " + retorno);
            throw new ApiUrlNoEncontradoException(retorno);
        } catch (NonUniqueResultException e) {
            retorno = "Varias URLs registradas";
            logger.severe(retorno + ": " + url);
            servicioLog.error("ServicioApiUrl::buscarPorUrl",
                    "Sistema " + sistema
                    + ", URL consultada: " + url + ", " + retorno);
            throw new ApiUrlNoEncontradoException(retorno);
        } catch (java.lang.NullPointerException e) {
            retorno = "Revisar el estado de la URL registrada";
            logger.severe(retorno + ": " + url);
            servicioLog.error("ServicioApiUrl::buscarPorUrl",
                    "Sistema " + sistema
                    + ", URL consultada: " + url + ", " + retorno);
            throw new ApiUrlNoEncontradoException(retorno);
        } finally {
            return retorno;
        }
    }
}
