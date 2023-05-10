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

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.NamedQuery;

/**
 * Representa un sistema transversal.
 *
 * @author Christian Espinosa <christian.espinosa@mintel.gob.ec>, Misael
 * Fernández
 */
@Entity
@NamedQuery(name = "Version.validarVersion", query = "SELECT v FROM Version v WHERE v.version= :version AND v.sistemaOperativo= :sistema_operativo AND v.aplicacion= :aplicacion")
//@NamedQuery(name = "Version.validarVersion", query = "SELECT v FROM Version v WHERE v.version= :version AND v.sistemaOperativo= :sistema_operativo AND v.sha = :sha")

public class Version implements Serializable {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private long id;

    private String sistemaOperativo;
    private String aplicacion;
    private String version;
    private String sha;
    private Boolean status;
    private String descripcion;
    private Date fechaLiberacion;
    private Date fechaObsoleto;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getSistemaOperativo() {
        return sistemaOperativo;
    }

    public void setSistemaOperativo(String sistemaOperativo) {
        this.sistemaOperativo = sistemaOperativo;
    }

    public String getAplicacion() {
        return aplicacion;
    }

    public void setAplicacion(String aplicacion) {
        this.aplicacion = aplicacion;
    }

    public String getVersion() {
        return version;
    }

    public void setVersion(String version) {
        this.version = version;
    }

    public String getSha() {
        return sha;
    }

    public void setSha(String sha) {
        this.sha = sha;
    }

    public Boolean getStatus() {
        return status;
    }

    public void setStatus(Boolean status) {
        this.status = status;
    }

    public String getDescripcion() {
        return descripcion;
    }

    public void setDescripcion(String descripcion) {
        this.descripcion = descripcion;
    }

    public Date getFechaLiberacion() {
        return fechaLiberacion;
    }

    public void setFechaLiberacion(Date fechaLiberacion) {
        this.fechaLiberacion = fechaLiberacion;
    }

    public Date getFechaObsoleto() {
        return fechaObsoleto;
    }

    public void setFechaObsoleto(Date fechaObsoleto) {
        this.fechaObsoleto = fechaObsoleto;
    }

    @Override
    public String toString() {
        return "Version{" + "id=" + id + ", sistemaOperativo=" + sistemaOperativo + ", aplicacion=" + aplicacion + ", version=" + version + ", sha=" + sha + ", status=" + status + ", descripcion=" + descripcion + ", fechaLiberacion=" + fechaLiberacion + ", fechaObsoleto=" + fechaObsoleto + '}';
    }
}
