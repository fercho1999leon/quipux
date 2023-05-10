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

import javax.ejb.ApplicationException;

/**
 * Excepcion arrojada en caso de problemas al almacenar el documento en el
 * sistema transversal.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@ApplicationException(rollback = true)
public class DocumentoNoExisteException extends Exception {

    private static final long serialVersionUID = -7132855600223954519L;

    public DocumentoNoExisteException() {
        super();
    }

    public DocumentoNoExisteException(String message) {
        super(message);
    }

    public DocumentoNoExisteException(String message, Throwable cause) {
        super(message, cause);
    }

    public DocumentoNoExisteException(Throwable cause) {
        super(cause);
    }
}
