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
package ec.gob.firmadigital.servicio.model;

import java.io.Serializable;
import java.util.Date;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;

/**
 * Representa una entrada de log.
 *
 * @author Ricardo Arguello <ricardo.arguello@soportelibre.com>
 */
@Entity
public class Log implements Serializable {

    /**
     * Severidad de la entrada de log
     */
    public enum Severidad {
        INFO, WARNING, ERROR
    }

    private static final long serialVersionUID = -4149737307219333116L;

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private long id;
    private Date fecha;
    private Severidad severidad;
    private String categoria;
    @Column(length = 300)
    private String descripcion;
    private String cedula;
    private String sistema;

    public Log() {
    }

    public Log(Severidad severidad, String categoria, String descripcion) {
        this.fecha = new Date();
        this.severidad = severidad;
        this.categoria = categoria;
        this.descripcion = descripcion;
    }

    public Log(Severidad severidad, String categoria, String descripcion, String sistema, String cedula) {
        this.fecha = new Date();
        this.severidad = severidad;
        this.categoria = categoria;
        this.descripcion = descripcion;
        this.sistema = sistema;
        this.cedula = cedula;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public Date getFecha() {
        return fecha;
    }

    public void setFecha(Date fecha) {
        this.fecha = fecha;
    }

    public Severidad getSeveridad() {
        return severidad;
    }

    public void setSeveridad(Severidad severidad) {
        this.severidad = severidad;
    }

    public String getCategoria() {
        return categoria;
    }

    public void setCategoria(String categoria) {
        this.categoria = categoria;
    }

    public String getDescripcion() {
        return descripcion;
    }

    public void setDescripcion(String descripcion) {
        this.descripcion = descripcion;
    }

    public String getCedula() {
        return cedula;
    }

    public void setCedula(String cedula) {
        this.cedula = cedula;
    }

    public String getSistema() {
        return sistema;
    }

    public void setSistema(String sistema) {
        this.sistema = sistema;
    }
}
